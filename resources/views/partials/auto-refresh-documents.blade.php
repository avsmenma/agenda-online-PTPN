@once
  <script>
    (function () {
      'use strict';

      const INTERVAL_MS = Number(window.documentAutoRefreshIntervalMs) || 10000;
      const IDLE_DELAY_MS = 5000;
      const SUBMIT_PAUSE_MS = 15000;
      let lastInteractionAt = Date.now();
      let submitPauseUntil = 0;
      let timerId = null;
      let isRunning = false;

      function markInteraction() {
        lastInteractionAt = Date.now();
      }

      ['keydown', 'mousedown', 'touchstart', 'wheel', 'input', 'change', 'focusin'].forEach((eventName) => {
        document.addEventListener(eventName, markInteraction, { capture: true, passive: true });
      });

      document.addEventListener('submit', () => {
        submitPauseUntil = Date.now() + SUBMIT_PAUSE_MS;
      }, true);

      function hasActiveTextControl() {
        const active = document.activeElement;
        if (!active || active === document.body) return false;

        return active.matches('input, textarea, select, [contenteditable="true"]')
          || Boolean(active.closest('.ie-editing, .ie-overlay-popup'));
      }

      function hasBlockingInteraction() {
        return Boolean(
          document.querySelector('.modal.show, .swal2-container.swal2-shown, .bulk-modal-overlay.show')
          || document.querySelector('.dropdown-menu.show, .select2-container--open')
          || document.querySelector('.ie-editing, .ie-saving, .ie-overlay-backdrop, .document-handler-select.is-saving')
          || document.querySelector('.document-checkbox:checked')
        );
      }

      function canAutoRefresh() {
        const btn = document.getElementById('btnRefreshTable');

        return typeof window.refreshDocumentTable === 'function'
          && !document.hidden
          && !isRunning
          && (!btn || (!btn.disabled && !btn.classList.contains('loading')))
          && Date.now() - lastInteractionAt >= IDLE_DELAY_MS
          && Date.now() >= submitPauseUntil
          && !hasActiveTextControl()
          && !hasBlockingInteraction();
      }

      async function run() {
        if (!canAutoRefresh()) return false;

        isRunning = true;
        try {
          const result = window.refreshDocumentTable({ silent: true, source: 'auto' });
          if (result && typeof result.then === 'function') {
            await result;
          }
          return true;
        } catch (error) {
          console.error('Auto refresh error:', error);
          return false;
        } finally {
          isRunning = false;
          lastInteractionAt = Date.now();
        }
      }

      function start() {
        if (timerId) return;
        timerId = setInterval(run, INTERVAL_MS);
      }

      function stop() {
        if (!timerId) return;
        clearInterval(timerId);
        timerId = null;
      }

      window.documentAutoRefresh = { start, stop, run, canAutoRefresh };

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
      } else {
        start();
      }
    })();
  </script>
@endonce
