<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();

$pageTitle = 'QR Scanner';
require __DIR__ . '/app/views/layouts/header.php';
?>
<div class="grid-2">
  <div class="card mb-3">
    <div class="card-header"><h3>Camera Scanner</h3></div>
    <div class="card-body">
      <p class="muted" style="margin-top:0;">Allow camera access, then point it at a product's QR label. If no camera is available, use manual search on the right.</p>
      <div id="qr-reader" style="width:100%;max-width:360px;"></div>
      <div id="qr-reader-results" class="muted" style="margin-top:10px;font-size:12.5px;"></div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header"><h3>Manual Lookup</h3></div>
    <div class="card-body">
      <form id="manualForm" onsubmit="return false;">
        <div class="form-group">
          <label>QR Code / Product Code</label>
          <input type="text" id="manualCode" placeholder="e.g. QR-PB0001" autofocus>
        </div>
        <button class="btn btn-primary btn-block" onclick="lookupCode(document.getElementById('manualCode').value)">Search</button>
      </form>

      <div id="productResult" style="margin-top:18px;"></div>
    </div>
  </div>
</div>

<?php
$extraScript = "<script src='https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js'></script>
<script>
const resultBox = document.getElementById('productResult');
const statusBox = document.getElementById('qr-reader-results');

function renderProduct(p) {
  if (!p) {
    resultBox.innerHTML = '<div class=\"alert alert-error\">No product found for that code.</div>';
    return;
  }
  let expiryBadge = '<span class=\"badge badge-gray\">No expiry</span>';
  if (p.expiry_status === 'red') expiryBadge = '<span class=\"badge badge-red\">Expired</span>';
  else if (p.expiry_status === 'yellow') expiryBadge = '<span class=\"badge badge-yellow\">Expiring Soon</span>';
  else if (p.expiry_status === 'green') expiryBadge = '<span class=\"badge badge-green\">Safe</span>';

  resultBox.innerHTML = `
    <div class=\"card\" style=\"box-shadow:none;\">
      <div class=\"card-body\">
        <h3 style=\"margin-top:0;\">\${p.name}</h3>
        <p class=\"muted\" style=\"margin:2px 0 10px;\">\${p.product_code} · \${p.category_name || 'Uncategorized'}</p>
        <table style=\"width:100%;font-size:13px;\">
          <tr><td class=\"muted\">Quantity</td><td>\${p.quantity} \${p.unit} \${p.low_stock ? '<span class=\"badge badge-yellow\">Low</span>' : ''}</td></tr>
          <tr><td class=\"muted\">Supplier</td><td>\${p.supplier_name || '—'}</td></tr>
          <tr><td class=\"muted\">Cost Price</td><td>₱\${p.cost_price}</td></tr>
          <tr><td class=\"muted\">Expiration</td><td>\${p.expiration_date || '—'} \${expiryBadge}</td></tr>
        </table>
        <a class=\"btn btn-primary btn-sm\" style=\"margin-top:12px;\" href=\"" . APP_URL . "/products.php?edit=\${p.id}\">Open in Products</a>
      </div>
    </div>`;
}

function lookupCode(code) {
  if (!code) return;
  statusBox.innerText = 'Looking up ' + code + '…';
  fetch('" . APP_URL . "/api/qr-lookup.php?code=' + encodeURIComponent(code))
    .then(r => r.json())
    .then(data => { statusBox.innerText = data.found ? 'Found: ' + data.product.name : 'Not found.'; renderProduct(data.found ? data.product : null); })
    .catch(() => { statusBox.innerText = 'Lookup failed. Please try again.'; });
}

document.getElementById('manualCode').addEventListener('keydown', function(e){ if(e.key==='Enter'){ lookupCode(this.value); }});

try {
  const scanner = new Html5Qrcode('qr-reader');
  Html5Qrcode.getCameras().then(cameras => {
    if (cameras && cameras.length) {
      scanner.start(cameras[0].id, { fps: 10, qrbox: 220 },
        decodedText => { lookupCode(decodedText); },
        () => {}
      );
    } else {
      statusBox.innerText = 'No camera detected on this device. Use manual search instead.';
    }
  }).catch(() => { statusBox.innerText = 'Camera access was denied or unavailable. Use manual search instead.'; });
} catch (e) {
  statusBox.innerText = 'Camera scanning is not supported in this browser. Use manual search instead.';
}
</script>";
require __DIR__ . '/app/views/layouts/footer.php';
