/* ============================================================
   UNIFY — Student Events  |  studentevents.js
   Features: RSVP, Remind Me, Event Feedback / Rating
   Multi-club aware via createClubView(clubId)
   ============================================================ */

(function () {
  'use strict';

  const AJAX_URL = '/index.php?page=studentevents_ajax';

  // ── Shared modal state ───────────────────────────────────
  let currentEventId   = null;
  let currentClubId    = null;
  let fbCurrentEventId = null;
  let fbSelectedStar   = 0;

  // ── Boot ─────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') { closeModal('detailOverlay'); closeModal('feedbackOverlay'); }
    });
    const $rev = document.getElementById('fbReview');
    const $cnt = document.getElementById('fbCharCount');
    if ($rev && $cnt) $rev.addEventListener('input', () => { $cnt.textContent = $rev.value.length; });

    const clubs = window.UNIFY_CLUBS || {};
    const ids = Object.keys(clubs);
    if (ids.length) ids.forEach(cid => createClubView(Number(cid)));
    else if (window.UNIFY) createClubView(null);
  });

  // ── Per-club view factory ─────────────────────────────────
  function createClubView(clubId) {
    const panelEl = clubId != null ? document.getElementById('evpanel-' + clubId) : document.body;
    if (!panelEl) return;

    function $p(id) {
      return panelEl.querySelector('#' + id + '_' + clubId) || document.getElementById(id + '_' + clubId);
    }

    const $grid  = $p('eventsGrid');
    const $empty = $p('emptyState');
    const $count = $p('filterCount');
    if (!$grid) return;

    const unify = (window.UNIFY_CLUBS && clubId != null) ? window.UNIFY_CLUBS[clubId] : window.UNIFY;
    if (!unify) return;

    const state = {
      activeFilter : 'all',
      view         : 'grid',
      search       : '',
      rsvped       : new Set((unify.rsvped   || []).map(Number)),
      reminded     : new Set((unify.reminded || []).map(Number)),
      myFeedback   : unify.myFeedback || {},
    };
    const events = unify.events || [];

    function getFiltered() {
      const q = state.search.trim().toLowerCase();
      return events.filter(e => {
        if (state.activeFilter === 'rsvped'   && !state.rsvped.has(Number(e.id)))   return false;
        if (state.activeFilter === 'reminded' && !state.reminded.has(Number(e.id))) return false;
        if (state.activeFilter !== 'all' && state.activeFilter !== 'rsvped' && state.activeFilter !== 'reminded') {
          if (e.status !== state.activeFilter) return false;
        }
        if (q) {
          const hay = (e.name+' '+(e.description||'')+' '+(e.location||'')).toLowerCase();
          if (!hay.includes(q)) return false;
        }
        return true;
      });
    }

    function renderEvents() {
      const list = getFiltered();
      $grid.innerHTML = list.map(e => renderCard(e)).join('');
      $grid.classList.toggle('list-view', state.view === 'list');
      if ($count) $count.textContent = `${list.length} event${list.length===1?'':'s'}`;
      if ($empty) $empty.style.display = list.length ? 'none' : '';
      $grid.querySelectorAll('[data-event-id]').forEach(el => {
        el.addEventListener('click', ev => {
          if (ev.target.closest('.ec-rsvp-btn,.ec-remind-btn,.ec-feedback-btn,.ec-details-btn')) return;
          openEventFromPanel(Number(el.dataset.eventId));
        });
      });
    }

    function renderCard(e) {
      const id=Number(e.id), isRsvped=state.rsvped.has(id), isReminded=state.reminded.has(id);
      const hasFb=!!state.myFeedback[String(id)], statusKey=e.status||'upcoming';
      const statusIcon={upcoming:'fa-clock',ongoing:'fa-circle-play',completed:'fa-check-double',cancelled:'fa-ban'}[statusKey]||'fa-clock';
      const canRsvp=statusKey==='upcoming'||statusKey==='ongoing';
      const canRemind=canRsvp, canFb=statusKey==='completed';
      const cid=clubId!=null?clubId:'null';
      const attendees=(e.attendees||[]).slice(0,4);
      const extra=Math.max(0,(Number(e.going_count)||0)-attendees.length);
      const avatarHtml=attendees.map(a=>`<span class="ec-avatar" title="${esc(a.name)}">${esc(a.avatar)}</span>`).join('')
        +(extra>0?`<span class="ec-avatar more">+${extra}</span>`:'');

      const rsvpBtn=canRsvp
        ?`<button class="ec-rsvp-btn ${isRsvped?'rsvped':''}" onclick="event.stopPropagation();toggleRSVPInPanel(${cid},${id})"><i class="fas ${isRsvped?'fa-circle-check':'fa-calendar-check'}"></i> ${isRsvped?"RSVP'd":'RSVP'}</button>`
        :`<button class="ec-rsvp-btn disabled" disabled><i class="fas ${statusIcon}"></i> ${statusKey==='completed'?'Ended':statusKey==='cancelled'?'Cancelled':'Closed'}</button>`;
      const remindBtn=canRemind
        ?`<button class="ec-remind-btn ${isReminded?'reminded':''}" title="${isReminded?'Remove reminder':'Set reminder'}" onclick="event.stopPropagation();toggleReminderInPanel(${cid},${id})"><i class="fas ${isReminded?'fa-bell-slash':'fa-bell'}"></i></button>`:'';
      const fbBtn=canFb
        ?`<button class="ec-feedback-btn ${hasFb?'fb-done':''}" title="${hasFb?'Edit feedback':'Rate event'}" onclick="event.stopPropagation();openFeedbackInPanel(${cid},${id})"><i class="fas fa-star"></i> ${hasFb?'Rated':'Rate'}</button>`:'';
      const ratingRow=canFb&&e.avg_rating>0
        ?`<div class="ec-rating-row">${renderStarsMini(e.avg_rating)}<span class="ec-rating-val">${e.avg_rating}</span><span class="ec-rating-total">(${e.total_ratings})</span></div>`:'';
      const remindBadge=isReminded?`<span class="ec-reminded-badge" title="Reminder set"><i class="fas fa-bell"></i></span>`:'';

      return `<article class="event-card" data-event-id="${id}">
        <div class="ec-banner">
          <div class="ec-date-chip"><div class="ec-date-month">${esc(e.month)}</div><div class="ec-date-day">${esc(e.day_num)}</div></div>
          <span class="ec-status-chip ${statusKey}"><i class="fas ${statusIcon}"></i> ${cap(statusKey)}</span>
          ${remindBadge}
        </div>
        <div class="ec-body">
          <div class="ec-when-row"><i class="fas fa-calendar"></i> ${esc(e.when_label)}</div>
          <h3 class="ec-title">${esc(e.name)}</h3>
          <div class="ec-meta">
            <div class="ec-meta-row"><i class="fas fa-clock"></i><span>${esc(e.time_range)}${e.duration?` · ${esc(e.duration)}`:''}</span></div>
            ${e.location?`<div class="ec-meta-row"><i class="fas fa-location-dot"></i><span>${esc(e.location)}</span></div>`:''}
          </div>
          ${e.description?`<p class="ec-desc">${esc(e.description)}</p>`:''}
          ${ratingRow}
          <div class="ec-attendees">
            <div class="ec-avatars">${avatarHtml||'<span class="ec-going-count">No RSVPs yet</span>'}</div>
            ${avatarHtml?`<span class="ec-going-count"><strong>${e.going_count}</strong> going</span>`:''}
          </div>
          <div class="ec-actions">${rsvpBtn}${fbBtn}<button class="ec-details-btn" onclick="event.stopPropagation();openEventFromPanelById(${cid},${id})"><i class="fas fa-circle-info"></i> Details</button>${remindBtn}</div>
        </div>
      </article>`;
    }

    function refreshStats() {
      let up=0,wk=0;
      events.forEach(e=>{if(e.status==='upcoming'||e.status==='ongoing')up++;if(e.is_this_week)wk++;});
      const su=$p('statUpcoming');if(su)su.textContent=up;
      const sr=$p('statRsvped');if(sr)sr.textContent=state.rsvped.size;
      const st=$p('statTotal');if(st)st.textContent=events.length;
      const sw=$p('statThisWeek');if(sw)sw.textContent=wk;
    }

    function toggleRSVP(eventId) {
      const id=Number(eventId), ev=events.find(x=>Number(x.id)===id);
      if(!ev) return;
      if(ev.status!=='upcoming'&&ev.status!=='ongoing'){showToast('RSVP closed for this event.','error');return;}
      if(state.rsvped.has(id)){state.rsvped.delete(id);ev.going_count=Math.max(0,(Number(ev.going_count)||0)-1);showToast('RSVP removed.','success');}
      else{state.rsvped.add(id);ev.going_count=(Number(ev.going_count)||0)+1;showToast(`You're going to "${ev.name}"!`,'success');}
      refreshStats();renderEvents();
      if(currentEventId===id&&currentClubId===clubId)syncModalRsvpBtn(id,events,state);
    }

    function toggleReminder(eventId) {
      const id=Number(eventId);
      const fd=new FormData();fd.append('action','toggle_reminder');fd.append('event_id',id);
      fetch(AJAX_URL,{method:'POST',body:fd}).then(r=>r.json()).then(data=>{
        if(!data.success){showToast(data.message||'Error.','error');return;}
        if(data.reminded)state.reminded.add(id);else state.reminded.delete(id);
        showToast(data.message,'success');renderEvents();
        if(currentEventId===id&&currentClubId===clubId)syncModalRemindBtn(id,state);
      }).catch(()=>showToast('Could not connect. Try again.','error'));
    }

    function openEventFromPanel(eventId) {
      const id=Number(eventId),e=events.find(x=>Number(x.id)===id);
      if(!e) return;
      currentEventId=id;currentClubId=clubId;
      set('mdMonth',e.month);set('mdDay',e.day_num);set('mdTitle',e.name);
      set('mdClubName',e.club_name+(e.club_acronym?` · ${e.club_acronym}`:''));
      set('mdWhen',`${e.weekday}, ${e.date_full} · ${e.when_label}`);
      set('mdTime',e.time_range);set('mdDuration',e.duration||'—');
      set('mdGoing',String(e.going_count||0));set('mdStatus',cap(e.status));
      set('mdLocation',e.location||'Location to be announced');
      set('mdDesc',e.description||'No description provided yet.');
      set('mdClubNameRow',e.club_name);set('mdClubCat',e.club_cat||'—');
      const $logo=document.getElementById('mdClubLogo'),
            $logoFb=document.getElementById('mdClubLogoFallback');
      if($logo&&$logoFb){
        if(e.club_logo){$logo.src=e.club_logo;$logo.style.display='';$logoFb.style.display='none';}
        else{$logo.style.display='none';$logoFb.style.display='flex';}
      }
      const $att=document.getElementById('mdAttendees');
      if($att){
        const arr=e.attendees||[],extra=Math.max(0,(Number(e.going_count)||0)-arr.length);
        $att.innerHTML=arr.length
          ?arr.map(a=>`<span class="modal-attendee-chip"><span class="ec-avatar">${esc(a.avatar)}</span>${esc(a.name)}</span>`).join('')
            +(extra>0?`<span class="modal-attendee-chip"><span class="ec-avatar more">+${extra}</span> and ${extra} more</span>`:'')
          :'<span class="modal-attendees-empty">No confirmed attendees yet.</span>';
      }
      syncModalRsvpBtn(id,events,state);
      syncModalRemindBtn(id,state);
      syncModalFeedbackBtn(id,e,state);
      document.getElementById('detailOverlay')?.classList.add('show');
    }

    function openFeedback(eventId) {
      const id=Number(eventId),ev=events.find(x=>Number(x.id)===id);
      if(!ev) return;
      fbCurrentEventId=id;
      set('fbEventTitle',ev.name);set('fbEventSub','Rate your experience');
      updateFbAvg(ev.avg_rating||0,ev.total_ratings||0);
      const prior=state.myFeedback[String(id)];
      fbSetStarUI(prior?prior.rating:0);
      const $rev=document.getElementById('fbReview'),
            $cnt=document.getElementById('fbCharCount');
      if($rev){$rev.value=prior?(prior.review||''):'';}
      if($cnt){$cnt.textContent=$rev?$rev.value.length:0;}
      window._fbClubView={state,events,renderEvents,clubId};
      document.getElementById('feedbackOverlay')?.classList.add('show');
    }

    window._clubViews=window._clubViews||{};
    window._clubViews[clubId]={toggleRSVP,toggleReminder,openEventFromPanel,openFeedback,renderEvents,state,events};
    renderEvents();
  }

  // ── Modal sync ────────────────────────────────────────────
  function syncModalRsvpBtn(id,events,state){
    const e=events.find(x=>Number(x.id)===Number(id));
    const $btn=document.getElementById('mdRsvpBtn');
    if(!$btn||!e) return;
    const canRsvp=e.status==='upcoming'||e.status==='ongoing';
    const isRsvped=state.rsvped.has(Number(id));
    $btn.style.display=canRsvp?'':'none';
    $btn.classList.toggle('rsvped',isRsvped);
    $btn.innerHTML=isRsvped?'<i class="fas fa-circle-check"></i> RSVP\'d — Cancel':'<i class="fas fa-calendar-check"></i> RSVP';
  }

  function syncModalRemindBtn(id,state){
    const $btn=document.getElementById('mdRemindBtn');if(!$btn) return;
    const ev=_findEvent(Number(id));
    if(!ev){$btn.style.display='none';return;}
    const canRemind=ev.status==='upcoming'||ev.status==='ongoing';
    const isReminded=state.reminded.has(Number(id));
    $btn.style.display=canRemind?'':'none';
    $btn.classList.toggle('reminded',isReminded);
    $btn.innerHTML=isReminded?'<i class="fas fa-bell-slash"></i> Remove Reminder':'<i class="fas fa-bell"></i> Remind Me';
  }

  function syncModalFeedbackBtn(id,ev,state){
    const $btn=document.getElementById('mdFeedbackBtn');if(!$btn) return;
    $btn.style.display=ev.status==='completed'?'':'none';
    const hasFb=!!state.myFeedback[String(Number(id))];
    $btn.innerHTML=hasFb?'<i class="fas fa-star"></i> Edit Feedback':'<i class="fas fa-star"></i> Rate Event';
  }

  function _findEvent(id){
    for(const v of Object.values(window._clubViews||{})){
      const f=v.events.find(x=>Number(x.id)===Number(id));if(f) return f;
    }
    return null;
  }

  // ── Feedback ──────────────────────────────────────────────
  window.fbSetStar=function(val){
    fbSelectedStar=val;fbSetStarUI(val);
    const hints=['','Poor','Fair','Good','Great','Excellent!'];
    const $h=document.getElementById('fbStarHint');if($h)$h.textContent=hints[val]||'';
  };

  function fbSetStarUI(val){
    fbSelectedStar=val;
    document.querySelectorAll('.fb-star').forEach(btn=>btn.classList.toggle('active',Number(btn.dataset.val)<=val));
  }

  function updateFbAvg(avg,total){
    const $s=document.getElementById('fbAvgScore'),
          $st=document.getElementById('fbAvgStars'),
          $l=document.getElementById('fbAvgLabel');
    if($s)$s.textContent=avg>0?avg.toFixed(1):'—';
    if($st)$st.innerHTML=avg>0?renderStarsMini(avg):'';
    if($l)$l.textContent=total>0?`${total} rating${total!==1?'s':''}`:'No ratings yet';
  }

  window.submitFeedback=function(){
    if(!fbCurrentEventId) return;
    if(!fbSelectedStar){showToast('Please select a star rating.','error');return;}
    const review=document.getElementById('fbReview')?.value.trim()||'';
    const $btn=document.getElementById('fbSubmitBtn');
    if($btn){$btn.disabled=true;$btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Saving…';}
    const fd=new FormData();
    fd.append('action','submit_feedback');fd.append('event_id',fbCurrentEventId);
    fd.append('rating',fbSelectedStar);fd.append('review',review);
    fetch(AJAX_URL,{method:'POST',body:fd}).then(r=>r.json()).then(data=>{
      if($btn){$btn.disabled=false;$btn.innerHTML='<i class="fas fa-paper-plane"></i> Submit Feedback';}
      if(!data.success){showToast(data.message||'Error.','error');return;}
      showToast(data.message,'success');closeModal('feedbackOverlay');
      const view=window._fbClubView;
      if(view){
        view.state.myFeedback[String(fbCurrentEventId)]={rating:fbSelectedStar,review};
        const evObj=view.events.find(x=>Number(x.id)===Number(fbCurrentEventId));
        if(evObj){evObj.avg_rating=data.avg_rating;evObj.total_ratings=data.total;}
        view.renderEvents();
        if(currentEventId===Number(fbCurrentEventId)&&evObj)syncModalFeedbackBtn(fbCurrentEventId,evObj,view.state);
      }
    }).catch(()=>{
      if($btn){$btn.disabled=false;$btn.innerHTML='<i class="fas fa-paper-plane"></i> Submit Feedback';}
      showToast('Could not connect. Try again.','error');
    });
  };

  function renderStarsMini(avg){
    let h='';
    for(let i=1;i<=5;i++){
      if(avg>=i)h+='<i class="fas fa-star ec-star filled"></i>';
      else if(avg>=i-0.5)h+='<i class="fas fa-star-half-stroke ec-star filled"></i>';
      else h+='<i class="far fa-star ec-star"></i>';
    }
    return h;
  }

  // ── Global entrypoints ────────────────────────────────────
  window.openEventFromPanelById=(cid,eid)=>(window._clubViews||{})[cid]?.openEventFromPanel(Number(eid));
  window.openEvent=eid=>{const v=Object.values(window._clubViews||{});if(v.length)v[0].openEventFromPanel(Number(eid));};
  window.toggleRSVPInPanel=(cid,eid)=>(window._clubViews||{})[cid]?.toggleRSVP(Number(eid));
  window.toggleReminderInPanel=(cid,eid)=>(window._clubViews||{})[cid]?.toggleReminder(Number(eid));
  window.openFeedbackInPanel=(cid,eid)=>(window._clubViews||{})[cid]?.openFeedback(Number(eid));
  window.modalToggleRSVP=()=>{
    if(currentEventId==null) return;
    const v=(window._clubViews||{})[currentClubId];
    if(v){v.toggleRSVP(currentEventId);syncModalRsvpBtn(currentEventId,v.events,v.state);}
  };
  window.modalToggleReminder=()=>{
    if(currentEventId==null) return;
    (window._clubViews||{})[currentClubId]?.toggleReminder(currentEventId);
  };
  window.modalOpenFeedback=()=>{
    if(currentEventId==null) return;
    const v=(window._clubViews||{})[currentClubId];
    if(v){closeModal('detailOverlay');setTimeout(()=>v.openFeedback(currentEventId),150);}
  };
  window.closeModal=id=>{
    document.getElementById(id)?.classList.remove('show');
    if(id==='detailOverlay'){currentEventId=null;currentClubId=null;}
    if(id==='feedbackOverlay'){fbCurrentEventId=null;fbSelectedStar=0;}
  };
  window.closeOverlay=(ev,id)=>{if(ev.target.id===id)closeModal(id);};
  window.filterEvents=()=>{
    const q=document.getElementById('searchInput')?.value||'';
    Object.values(window._clubViews||{}).forEach(v=>{v.state.search=q;v.renderEvents();});
  };
  window.setCat=(btn,cat)=>{
    const panel=btn.closest('.club-events-panel')||document.body;
    const cid=panel.id?parseInt(panel.id.replace('evpanel-','')):null;
    const view=(window._clubViews||{})[cid];if(!view) return;
    view.state.activeFilter=cat;
    panel.querySelectorAll('.pill').forEach(p=>p.classList.remove('active'));
    btn.classList.add('active');view.renderEvents();
  };
  window.setView=(viewName,btn)=>{
    const panel=btn.closest('.club-events-panel')||document.body;
    const cid=panel.id?parseInt(panel.id.replace('evpanel-','')):null;
    const view=(window._clubViews||{})[cid];if(!view) return;
    view.state.view=viewName;
    panel.querySelectorAll('.view-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');view.renderEvents();
  };

  // ── Toast ─────────────────────────────────────────────────
  let _tt;
  function showToast(msg,type='success'){
    const $t=document.getElementById('crudToast');if(!$t) return;
    $t.textContent=msg;$t.className='crud-toast show '+type;
    clearTimeout(_tt);_tt=setTimeout(()=>$t.classList.remove('show'),2600);
  }
  window.showToast=showToast;

  // ── Utils ─────────────────────────────────────────────────
  function set(id,val){const el=document.getElementById(id);if(el)el.textContent=val??'—';}
  function cap(s){return s?s.charAt(0).toUpperCase()+s.slice(1):'';}
  function esc(s){
    if(s==null) return '';
    return String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
                    .replaceAll('"','&quot;').replaceAll("'",'&#39;');
  }
})();
