// Optional rendered integration test. Install Playwright outside the repository;
// set MMGSS_PLAYWRIGHT_PATH to its index.mjs, and use the isolated *_test DB.
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import path from 'node:path';
import os from 'node:os';
import { pathToFileURL } from 'node:url';
if (process.env.MMGSS_TEST_ALLOW_WRITE !== '1') throw Error('Set MMGSS_TEST_ALLOW_WRITE=1 and use the dedicated local test servers.');
const { chromium } = await import(process.env.MMGSS_PLAYWRIGHT_PATH ? pathToFileURL(process.env.MMGSS_PLAYWRIGHT_PATH).href : 'playwright');
const browser = await chromium.launch({headless:true, ...(process.env.MMGSS_CHROME_PATH ? {executablePath:process.env.MMGSS_CHROME_PATH} : {})});
const context = await browser.newContext({viewport:{width:1440,height:1000}});
const admin = await context.newPage(), publicPage = await context.newPage();
const adminUrl = process.env.MMGSS_TEST_ADMIN_URL || 'http://127.0.0.1:8089';
const publicUrl = process.env.MMGSS_TEST_PUBLIC_URL || 'http://127.0.0.1:8088';
for (const url of [adminUrl, publicUrl]) { if (!['127.0.0.1', 'localhost', '[::1]'].includes(new URL(url).hostname)) throw Error('Browser tests are restricted to loopback servers.'); }
const out = process.env.MMGSS_SCREENSHOT_DIR || path.join(os.tmpdir(),'codex-mmgss-recruitment','screenshots'); await fs.mkdir(out,{recursive:true});
const errors = [], failedRequests = [];
for(const page of [admin,publicPage]){
 page.on('pageerror',e=>errors.push(e.message));
 page.on('console',msg=>{if(msg.type()==='error')errors.push(msg.text());});
 page.on('requestfailed',r=>failedRequests.push({url:r.url(),error:r.failure()?.errorText}));
}
const expect = async (locator, n=1) => assert.equal(await locator.count(),n);
const save = async () => {await admin.getByRole('button',{name:'Save opportunity',exact:true}).click();await admin.getByRole('button',{name:'Confirm',exact:true}).click();await admin.getByRole('status').filter({hasText:'Job saved'}).waitFor();};
try {
 await admin.goto(adminUrl+'/login.php'); assert.match(await admin.title(),/Myanmar GSS Admin/);
 await admin.screenshot({path:path.join(out,'login-desktop.png')});
 await admin.getByLabel('Username',{exact:true}).fill('admin');await admin.getByLabel('Password',{exact:true}).fill('admin123');await admin.getByRole('button',{name:'Sign in securely'}).click();await admin.getByRole('heading',{name:'Recruitment overview'}).waitFor();
 await admin.screenshot({path:path.join(out,'dashboard-desktop.png')});
 await admin.setViewportSize({width:390,height:844});await admin.screenshot({path:path.join(out,'dashboard-mobile.png')});
 assert(await admin.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'mobile admin has no page overflow');
 await admin.setViewportSize({width:1440,height:1000});
 await admin.getByRole('navigation',{name:'Job pages'}).getByRole('link',{name:'2',exact:true}).click();await admin.getByText('Page 2 of 2',{exact:true}).waitFor();
 await publicPage.goto(publicUrl+'/recruitment');await publicPage.locator('.jobs-grid .job-item').first().waitFor();
 assert.match(await publicPage.title(),/Myanmar GSS/);assert.equal(new URL(publicPage.url()).pathname,'/recruitment');
 await publicPage.locator('[data-language="en"]').click();await publicPage.waitForFunction(()=>document.documentElement.lang==='en');
 await expect(publicPage.locator('.jobs-grid .job-item'),2);await expect(publicPage.locator('.past-intake-grid .past-intake-item'),16);
 await publicPage.locator('.filter[data-filter="professional"]').click();await expect(publicPage.locator('.jobs-grid .job-item'),0);assert(await publicPage.locator('#recruitmentEmpty').isVisible());
 await publicPage.locator('.filter[data-filter="all"]').click();
 await admin.goto(adminUrl+'/job.php');
 const title='Browser QA opportunity';
 for(const [l,heading] of [['en',title],['ja','ブラウザー検証求人']]){
  await admin.locator('#'+l+'_title').fill(heading);await admin.locator('#'+l+'_summary').fill('QA summary');await admin.locator('#'+l+'_description').fill('<img src=x onerror="window.xssExecuted=true">\nPlain text description');await admin.locator('#'+l+'_location').fill('Tokyo');await admin.locator('#'+l+'_requirements').fill('QA requirements');
 }
 await admin.locator('#status').selectOption('open');await save();
 const jobUrl=admin.url();
 await publicPage.reload();await publicPage.getByRole('heading',{name:title,exact:true}).waitFor();
 let card=publicPage.locator('.job-item').filter({has:publicPage.getByRole('heading',{name:title,exact:true})});await expect(card);
 await card.getByRole('button',{name:'View role details'}).click();await publicPage.locator('#jobModal.is-open').waitFor();
 assert.match(await publicPage.locator('#modalSummary').textContent(),/<img src=x/);assert.equal(await publicPage.locator('#modalSummary img').count(),0);assert.equal(await publicPage.evaluate(()=>!!window.xssExecuted),false);
 await publicPage.keyboard.press('Tab');await publicPage.keyboard.press('Escape');assert(await publicPage.locator('#jobModal').isHidden());
 await publicPage.locator('[data-language="ja"]').click();await publicPage.getByRole('heading',{name:'ブラウザー検証求人',exact:true}).waitFor();
 await publicPage.locator('[data-language="en"]').click();
 await admin.locator('#en_title').fill(title+' updated');await save();
 await publicPage.reload();await publicPage.getByRole('heading',{name:title+' updated',exact:true}).waitFor();
 await admin.goto(adminUrl+'/index.php?q='+encodeURIComponent(title));
 const row=admin.getByRole('row').filter({hasText:title+' updated'});
 await row.getByRole('button',{name:'Close',exact:true}).click();await admin.getByRole('button',{name:'Cancel',exact:true}).click();await expect(row.getByText('Open',{exact:true}));
 await row.getByRole('button',{name:'Close',exact:true}).click();await admin.getByRole('button',{name:'Confirm',exact:true}).click();await admin.getByRole('status').filter({hasText:'Job updated'}).waitFor();
 await publicPage.reload();await publicPage.locator('.past-intake-item').filter({hasText:title+' updated'}).waitFor();await expect(publicPage.locator('.job-item').filter({hasText:title+' updated'}),0);
 await admin.goto(adminUrl+'/index.php?q='+encodeURIComponent(title));await admin.getByRole('row').filter({hasText:title+' updated'}).getByRole('button',{name:'Reopen',exact:true}).click();await admin.getByRole('button',{name:'Confirm',exact:true}).click();await admin.getByRole('status').filter({hasText:'Job updated'}).waitFor();
 await publicPage.reload();await publicPage.locator('.job-item').filter({hasText:title+' updated'}).waitFor();await expect(publicPage.locator('.past-intake-item').filter({hasText:title+' updated'}),0);
 await publicPage.locator('.recruitment-section').scrollIntoViewIfNeeded();await publicPage.screenshot({path:path.join(out,'recruitment-desktop.png')});
 await publicPage.setViewportSize({width:390,height:844});await publicPage.screenshot({path:path.join(out,'recruitment-mobile.png')});assert(await publicPage.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'mobile recruitment has no overflow');
 await publicPage.locator('.job-item').filter({hasText:title+' updated'}).getByRole('button',{name:'View role details'}).click();await publicPage.locator('#jobModal.is-open').waitFor();await publicPage.waitForTimeout(450);await publicPage.screenshot({path:path.join(out,'role-modal-mobile.png')});await publicPage.keyboard.press('Escape');
 await admin.goto(jobUrl);await admin.locator('#status').selectOption('archived');await save();
 await admin.goto(adminUrl+'/index.php?q='+encodeURIComponent(title));await admin.getByRole('row').filter({hasText:title+' updated'}).getByRole('button',{name:'Delete',exact:true}).click();await admin.getByRole('button',{name:'Confirm',exact:true}).click();await admin.getByRole('status').filter({hasText:'Job updated'}).waitFor();
 await publicPage.goto(publicUrl+'/index.html');await publicPage.locator('.nav-shell').waitFor();await publicPage.waitForTimeout(500);
 const localErrors=errors.filter(e=>!e.includes('net::ERR_'));assert.deepEqual(localErrors,[],'no app JavaScript errors, including unrelated homepage');
 assert.deepEqual(failedRequests.filter(r=>r.url.startsWith(adminUrl)||r.url.startsWith(publicUrl)),[],'no failed local assets/API requests');
 console.log(JSON.stringify({result:'PASS',flows:['login','create and publish','edit','cancel confirmation','close','reopen','archive','delete','public category filters','English/Japanese switch','plain-text XSS safety','mobile role modal'],viewports:['1440x1000','390x844'],appErrors:localErrors,failedRequests,screenshots:out},null,2));
} finally {await browser.close();}
