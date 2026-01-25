/* /assets/js/copy.js
 * Drop-in: injects CSS + modal HTML + copy() function.
 * Usage in HTML: onclick="copy('goodboy2ny')"
 */
(function () {
    // ---------------------------
    // 1) Inject CSS (prettified, responsive)
    // ---------------------------
    var CSS = `
  :root{
    --copy-accent: #D72171;
    --copy-accent-dark: #b81a5a;
    --copy-text: #1a1a1a;
    --copy-muted: #666;
    --copy-border: rgba(0,0,0,0.10);
    --copy-shadow: 0 24px 70px rgba(0,0,0,0.25);
    --copy-radius: 18px;
  }
  
  /* Overlay */
  .copy-modal-overlay{
    position: fixed;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 18px;
    z-index: 99999;
  
    background: rgba(0,0,0,0.42);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
  }
  
  /* Modal */
  .copy-modal{
    width: min(560px, 92vw);
    max-width: 560px;
    background: #fff;
    border-radius: var(--copy-radius);
    border: 1px solid var(--copy-border);
    box-shadow: var(--copy-shadow);
    overflow: hidden;
  
    transform: translateY(10px) scale(0.98);
    opacity: 0;
    transition: transform 160ms ease, opacity 160ms ease;
  }
  
  .copy-modal-overlay.show .copy-modal{
    transform: translateY(0) scale(1);
    opacity: 1;
  }
  
  /* Header area */
  .copy-modal-header{
    padding: 22px 22px 10px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
  }
  
  .copy-modal-badge{
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: rgba(215,33,113,0.10);
    border: 1px solid rgba(215,33,113,0.18);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
  }
  
  .copy-modal-badge svg{
    width: 22px;
    height: 22px;
    fill: var(--copy-accent);
  }
  
  .copy-modal-title-wrap{
    flex: 1;
  }
  
  .copy-modal-title{
    margin: 0;
    font-size: 22px;
    line-height: 1.25;
    font-weight: 800;
    color: var(--copy-text);
    letter-spacing: 0.2px;
  }
  
  .copy-modal-sub{
    margin: 8px 0 0;
    font-size: 15px;
    line-height: 1.6;
    color: var(--copy-muted);
    word-break: break-word;
  }
  
  /* Body */
  .copy-modal-body{
    padding: 6px 22px 18px;
  }
  
  /* Copy value pill */
  .copy-modal-pill{
    margin: 10px 0 0;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 14px;
    background: rgba(0,0,0,0.03);
    border: 1px solid rgba(0,0,0,0.08);
    max-width: 100%;
  }
  
  .copy-modal-pill code{
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 14px;
    color: #222;
    background: transparent;
    padding: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: min(420px, 68vw);
  }
  
  .copy-modal-hint{
    font-size: 13px;
    color: rgba(0,0,0,0.45);
    margin-top: 10px;
  }
  
  /* Footer */
  .copy-modal-footer{
    padding: 16px 22px 22px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
  }
  
  .copy-modal-ok{
    background: var(--copy-accent);
    color: #fff;
    border: none;
    border-radius: 14px;
    padding: 12px 18px;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    transition: transform 120ms ease, background 120ms ease, box-shadow 120ms ease;
    box-shadow: 0 10px 22px rgba(215,33,113,0.22);
  }
  .copy-modal-ok:hover{
    background: var(--copy-accent-dark);
    transform: translateY(-1px);
    box-shadow: 0 14px 28px rgba(215,33,113,0.26);
  }
  .copy-modal-ok:active{
    transform: translateY(0);
  }
  
  /* Mobile adjustments */
  @media (max-width: 420px){
    .copy-modal-title{ font-size: 20px; }
    .copy-modal-badge{ width: 40px; height: 40px; border-radius: 13px; }
    .copy-modal-footer{ justify-content: center; }
    .copy-modal-ok{ width: 100%; }
  }
  `;
  
    function injectCSS() {
      if (document.getElementById("copyjs-style")) return;
      var style = document.createElement("style");
      style.id = "copyjs-style";
      style.type = "text/css";
      style.appendChild(document.createTextNode(CSS));
      document.head.appendChild(style);
    }
  
    // ---------------------------
    // 2) Inject Modal HTML (no click-away close)
    // ---------------------------
    function injectModal() {
      if (document.getElementById("copy-modal-overlay")) return;
  
      var overlay = document.createElement("div");
      overlay.id = "copy-modal-overlay";
      overlay.className = "copy-modal-overlay";
      overlay.setAttribute("aria-hidden", "true");
  
      overlay.innerHTML = `
        <div class="copy-modal" role="dialog" aria-modal="true" aria-labelledby="copy-modal-title">
          <div class="copy-modal-header">
            <div class="copy-modal-badge" aria-hidden="true">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M9.0 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"></path>
              </svg>
            </div>
  
            <div class="copy-modal-title-wrap">
              <h3 class="copy-modal-title" id="copy-modal-title">已复制</h3>
              <div class="copy-modal-sub" id="copy-modal-msg">已复制到剪贴板</div>
  
              <div class="copy-modal-body">
                <div class="copy-modal-pill" id="copy-modal-pill" style="display:none;">
                  <code id="copy-modal-code"></code>
                </div>
                <div class="copy-modal-hint" id="copy-modal-hint" style="display:none;">
                  你可以在需要的地方直接粘贴使用
                </div>
              </div>
            </div>
          </div>
  
          <div class="copy-modal-footer">
            <button type="button" style="outline: none;" class="copy-modal-ok" id="copy-modal-ok">确定</button>
          </div>
        </div>
      `;
  
      document.body.appendChild(overlay);
  
      var okBtn = document.getElementById("copy-modal-ok");
      if (okBtn) okBtn.addEventListener("click", closeCopyModal);
  
      // Close on ESC only
      document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") closeCopyModal();
      });
    }
  
    // ---------------------------
    // 3) Modal open/close
    // ---------------------------
    function openCopyModal(message, value) {
      var overlay = document.getElementById("copy-modal-overlay");
      var msgEl = document.getElementById("copy-modal-msg");
      var pill = document.getElementById("copy-modal-pill");
      var codeEl = document.getElementById("copy-modal-code");
      var hint = document.getElementById("copy-modal-hint");
      if (!overlay || !msgEl) return;
  
      msgEl.textContent = message || "已复制到剪贴板";
  
      if (value != null && String(value).trim() !== "" && pill && codeEl && hint) {
        pill.style.display = "inline-flex";
        hint.style.display = "block";
        codeEl.textContent = String(value);
      } else if (pill && hint) {
        pill.style.display = "none";
        hint.style.display = "none";
      }
  
      overlay.style.display = "flex";
      overlay.setAttribute("aria-hidden", "false");
  
      requestAnimationFrame(function () {
        overlay.classList.add("show");
      });
  
      var okBtn = document.getElementById("copy-modal-ok");
      if (okBtn) okBtn.focus();
    }
  
    function closeCopyModal() {
      var overlay = document.getElementById("copy-modal-overlay");
      if (!overlay) return;
  
      overlay.classList.remove("show");
      setTimeout(function () {
        overlay.style.display = "none";
        overlay.setAttribute("aria-hidden", "true");
      }, 170);
    }
  
    window.closeCopyModal = closeCopyModal;
  
    // ---------------------------
    // 4) Clipboard logic
    // ---------------------------
    async function copyToClipboard(text) {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        return true;
      }
  
      var ta = document.createElement("textarea");
      ta.value = text;
      ta.setAttribute("readonly", "");
      ta.style.position = "fixed";
      ta.style.left = "-9999px";
      ta.style.top = "-9999px";
      document.body.appendChild(ta);
      ta.select();
  
      try {
        var ok = document.execCommand("copy");
        document.body.removeChild(ta);
        return ok;
      } catch (e) {
        document.body.removeChild(ta);
        return false;
      }
    }
  
    // ---------------------------
    // 5) Public API: copy('blahblah')
    // ---------------------------
    window.copy = function (text) {
      injectCSS();
      injectModal();
  
      var value = (text == null) ? "" : String(text);
  
      copyToClipboard(value)
        .then(function (ok) {
          if (ok) {
            openCopyModal("你已复制到剪贴板", value);
          } else {
            openCopyModal("复制失败，请手动复制", "");
          }
        })
        .catch(function () {
          openCopyModal("复制失败，请手动复制", "");
        });
    };
  
    // Inject early
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", function () {
        injectCSS();
        injectModal();
      });
    } else {
      injectCSS();
      injectModal();
    }
  })();
  