

/* ── char counter + form logic (moved from inline) ── */
// Char counter
  const descInput = document.getElementById('crDescription');
  const descCount = document.getElementById('crDescCount');
  if (descInput && descCount) {
    descInput.addEventListener('input', () => { descCount.textContent = descInput.value.length; });
  }

  async function submitClubRequest() {
    const name        = document.getElementById('crName')?.value.trim();
    const acronym     = document.getElementById('crAcronym')?.value.trim();
    const category    = document.getElementById('crCategory')?.value;
    const description = document.getElementById('crDescription')?.value.trim();
    const room        = document.getElementById('crRoom')?.value.trim();
    const founded     = document.getElementById('crFounded')?.value.trim();

    if (!name)        { showToast('Club name is required.', 'error'); return; }
    if (!category)    { showToast('Please select a category.', 'error'); return; }
    if (!description) { showToast('Description is required.', 'error'); return; }

    const btn = document.getElementById('crSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting…';

    const fd = new FormData();
    fd.append('action', 'submit');
    fd.append('name', name);
    fd.append('acronym', acronym);
    fd.append('category', category);
    fd.append('description', description);
    fd.append('room', room);
    fd.append('founded', founded);

    try {
      const res  = await fetch('index.php?page=club_request_handler', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.success) {
        showToast(data.message || 'Request submitted!', 'success');
        setTimeout(() => window.location.reload(), 1800);
      } else {
        showToast(data.message || 'Submission failed.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Request';
      }
    } catch (e) {
      showToast('Network error. Please try again.', 'error');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Request';
    }
  }

  function showToast(msg, type) {
    const t = document.getElementById('cr-toast');
    if (!t) return;
    t.textContent = msg;
    t.className = `cr-toast cr-toast-${type} visible`;
    setTimeout(() => t.classList.remove('visible'), 3400);
  }
