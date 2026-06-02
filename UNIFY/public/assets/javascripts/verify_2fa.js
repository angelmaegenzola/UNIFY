

/* ── OTP box logic (moved from inline) ── */
(function () {
  const boxes  = document.querySelectorAll('.otp-box');
  const hidden = document.getElementById('otp_code');
  const form   = document.getElementById('otpForm');
  const btn    = document.getElementById('verifyBtn');

  // ── Digit navigation ──────────────────────────────────────
  boxes.forEach((box, idx) => {
    box.addEventListener('input', e => {
      e.target.value = e.target.value.replace(/\D/g, '').slice(-1);
      e.target.classList.toggle('filled', !!e.target.value);
      if (e.target.value && idx < boxes.length - 1) boxes[idx + 1].focus();
      syncHidden();
    });

    box.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !box.value && idx > 0) {
        boxes[idx - 1].focus();
        boxes[idx - 1].value = '';
        boxes[idx - 1].classList.remove('filled');
        syncHidden();
      }
    });

    box.addEventListener('paste', e => {
      e.preventDefault();
      const digits = (e.clipboardData || window.clipboardData)
        .getData('text').replace(/\D/g, '').slice(0, 6);
      digits.split('').forEach((ch, i) => {
        if (boxes[i]) { boxes[i].value = ch; boxes[i].classList.add('filled'); }
      });
      boxes[Math.min(digits.length, boxes.length - 1)].focus();
      syncHidden();
    });
  });

  function syncHidden() {
    hidden.value = [...boxes].map(b => b.value).join('');
  }

  // ── Submit ────────────────────────────────────────────────
  form.addEventListener('submit', e => {
    syncHidden();
    if (hidden.value.length < 6) {
      e.preventDefault();
      boxes.forEach(b => b.classList.add('shake'));
      setTimeout(() => boxes.forEach(b => b.classList.remove('shake')), 400);
      return;
    }
    btn.disabled = true;
    btn.querySelector('.spinner').style.display = 'block';
    btn.querySelector('.btn-label').textContent  = 'Verifying…';
  });

  // ── TOTP countdown ────────────────────────────────────────
  const bar   = document.getElementById('timerBar');
  const secEl = document.getElementById('timerSec');
  (function tick() {
    const left = 30 - (Math.floor(Date.now() / 1000) % 30);
    bar.style.width = (left / 30 * 100) + '%';
    secEl.textContent = left + 's';
    bar.classList.toggle('urgent', left <= 8);
    setTimeout(tick, 1000);
  })();
})();
