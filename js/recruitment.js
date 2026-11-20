(() => {
  'use strict';
  const $ = s => document.querySelector(s);
  let jobs = [], openFilter = 'all', closedFilter = 'all', selected = null, returnFocus = null, busy = false, loaded = false;
  const modal = $('#jobModal'), panel = modal.querySelector('.modal-panel');
  const en = {loading: 'Loading opportunities…', error: 'Opportunities could not be refreshed. Please try again or contact our team.', retry: 'Try again', open: 'Open opportunity', closed: 'Closed', empty: 'No closed roles match this category.'};
  const ja = {loading: '求人情報を読み込み中…', error: '求人情報を更新できませんでした。再度お試しいただくか、お問い合わせください。', retry: '再試行', open: '募集中', closed: '募集終了', empty: 'このカテゴリーに募集終了の求人はありません。'};
  const lang = () => document.documentElement.lang === 'ja' ? 'ja' : 'en';
  const labels = () => lang() === 'ja' ? ja : en;
  const t = (key, fallback) => window.MMGSSI18n?.t(key, undefined, fallback) || fallback;
  const category = job => t('recruitment.types.' + ({professional:'professional', ssw:'ssw', 'technical-intern':'intern'}[job.category]), job.category);
  function data(job) {
    const english = job.translations.en || {}, translated = job.translations[lang()] || {};
    return Object.fromEntries(Object.keys(english).map(key => [key, translated[key] || english[key]]));
  }
  function el(tag, cls, text) { const node = document.createElement(tag); if (cls) node.className = cls; if (text !== undefined) node.textContent = text; return node; }
  function card(job, index) {
    const d = data(job), open = job.status === 'open';
    const article = el('article', open ? 'job-item reveal show' : 'past-intake-item');
    article.dataset.id = job.id;
    article.append(el('span', 'job-status ' + (open ? 'job-status-open' : 'job-status-closed'), open ? labels().open : labels().closed));
    if (open) { const top = el('div','job-topline'); top.append(el('span','',String(index + 1).padStart(2,'0')),el('span','',category(job))); article.append(top); }
    article.append(el('h3','',d.title),el('p', open ? '' : 'past-intake-summary',d.summary));
    if (open) {
      const meta = el('div','job-meta');
      for (const [field, label] of [['location',t('recruitment.jobMeta.location','Location')],['japanese',t('recruitment.jobMeta.japanese','Japanese')],['salary',t('recruitment.jobMeta.salary','Salary')]]) {
        const item = el('span'); item.append(el('small','',label),el('strong','',d[field] || '—')); meta.append(item);
      } article.append(meta);
    } else article.append(el('span','past-intake-type',category(job)));
    const button = el('button',open ? 'btn btn-red job-view' : 'btn btn-light job-view past-intake-view',t('recruitment.viewDetails','View role details'));
    button.type='button'; button.addEventListener('click',()=>showModal(job,button)); article.append(button);
    return article;
  }
  function render() {
    const open = jobs.filter(j=>j.status==='open'), closed=jobs.filter(j=>j.status==='closed');
    const visible = open.filter(j=>openFilter==='all'||j.category===openFilter);
    const past = closed.filter(j=>closedFilter==='all'||j.category===closedFilter);
    $('.jobs-grid').replaceChildren(...visible.map(card));
    $('.past-intake-grid').replaceChildren(...past.map(card));
    if (!past.length && loaded) $('.past-intake-grid').append(el('p','',labels().empty));
    $('#roleCount').textContent=visible.length; $('#heroRoleCount').textContent=open.length;
    $('#recruitmentEmpty').hidden=!loaded||visible.length!==0;
    $('#recruitmentRetry').textContent=labels().retry;
    if(selected) { const latest=jobs.find(j=>j.id===selected.id); if(latest){selected=latest;fillModal(latest);} else closeModal(); }
  }
  function fillModal(job) {
    const d=data(job); $('#modalRole').textContent=d.title;
    $('#modalType').textContent=job.status==='closed'?labels().closed:category(job);
    for(const [id,field] of Object.entries({Location:'location',Salary:'salary',Language:'japanese',Duration:'contract',Summary:'description',Requirements:'requirements'})){
      $('#modal'+id).textContent=d[field]||t('common.detailsOnRequest','Details available on request');
    }
  }
  function showModal(job,button){selected=job;returnFocus=button;fillModal(job);modal.hidden=false;document.body.classList.add('modal-open');requestAnimationFrame(()=>{modal.classList.add('is-open');panel.scrollTop=0;$('#modalClose').focus();});}
  function closeModal(){selected=null;modal.classList.remove('is-open');document.body.classList.remove('modal-open');modal.hidden=true;(returnFocus?.isConnected?returnFocus:$('.filter.active'))?.focus();}
  $('#modalClose').addEventListener('click',closeModal);$('#modalCloseBottom').addEventListener('click',closeModal);
  modal.addEventListener('click',event=>{if(event.target===modal)closeModal();});
  document.addEventListener('keydown',event=>{
    if(modal.hidden)return;
    if(event.key==='Escape')closeModal();
    if(event.key==='Tab'){
      const focusable=[...panel.querySelectorAll('button,a[href]')],first=focusable[0],last=focusable.at(-1);
      if(event.shiftKey&&document.activeElement===first){event.preventDefault();last.focus();}
      else if(!event.shiftKey&&document.activeElement===last){event.preventDefault();first.focus();}
    }
  });
  for(const [selector,key] of [['.filter[data-filter]','filter'],['.past-filter[data-past-filter]','pastFilter']]){
    const buttons=[...document.querySelectorAll(selector)];
    buttons.forEach(button=>{button.setAttribute('aria-pressed',String(button.classList.contains('active')));button.addEventListener('click',()=>{buttons.forEach(b=>{b.classList.toggle('active',b===button);b.setAttribute('aria-pressed',String(b===button));});if(key==='filter')openFilter=button.dataset[key];else closedFilter=button.dataset[key];render();});});
  }
  async function refresh(){
    if(busy)return;busy=true;$('.jobs-grid').setAttribute('aria-busy','true');
    if(!loaded)$('#recruitmentStatus').textContent=labels().loading;
    try{
      const response=await fetch('api/jobs.php',{cache:'no-store',credentials:'omit',signal:AbortSignal.timeout(15000)});
      if(!response.ok)throw Error('Listing unavailable');
      const payload=await response.json();if(!Array.isArray(payload.jobs))throw Error('Invalid listing');
      jobs=payload.jobs;loaded=true;$('#recruitmentStatus').textContent='';$('#recruitmentRetry').hidden=true;render();
    }catch{ $('#recruitmentStatus').textContent=labels().error;$('#recruitmentRetry').hidden=false; }
    finally{busy=false;$('.jobs-grid').setAttribute('aria-busy','false');}
  }
  $('#recruitmentRetry').addEventListener('click',refresh);
  for(const event of ['i18n:ready','i18n:languagechanged'])document.addEventListener(event,()=>{render();if(!loaded)$('#recruitmentStatus').textContent=labels().loading;});
  document.addEventListener('visibilitychange',()=>{if(!document.hidden)refresh();});
  setInterval(()=>{if(!document.hidden)refresh();},60000);
  refresh();
})();
