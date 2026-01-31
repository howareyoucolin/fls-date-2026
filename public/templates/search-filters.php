<?php
$gender = $gender ?? 'all';
$photo  = $photo ?? 'any';
$minAge = isset($minAge) ? (int)$minAge : 18;
$maxAge = isset($maxAge) ? (int)$maxAge : 60;

$minAge = max(18, min(99, $minAge));
$maxAge = max(18, min(99, $maxAge));
if ($minAge > $maxAge) { $tmp = $minAge; $minAge = $maxAge; $maxAge = $tmp; }

$resetUrl = SITE_URL . '/search';

function gender_label($g) {
  if ($g === 'm') return '男';
  if ($g === 'f') return '女';
  return '不限';
}
function photo_label($p) {
  if ($p === 'with') return '有照片';
  if ($p === 'without') return '无照片';
  return '不限';
}

// Compact summary string (server-rendered initial)
$summaryText = gender_label($gender) . ' | ' . photo_label($photo) . ' | ' . $minAge . ' - ' . $maxAge . ' 岁';
?>

<style>
.search-filters { margin: 10px 0 20px; }

.search-filters-card {
  border: 1px solid #e0e0e0;
  background: #fff;
  padding: 14px 14px 12px;
}

/* Mobile-only header (toggle area) */
.search-filters-mobilehead {
  display: none;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 10px;
}

.search-filters-toggle {
  display: grid;
  gap: 4px;
  cursor: pointer;
  user-select: none;
}

.search-filters-toggle .title {
  font-size: 13px;
  font-weight: 800;
  color: rgba(0,0,0,0.70);
  letter-spacing: 0.2px;
  line-height: 1.1;
}

.search-filters-toggle .summary {
  font-size: 12px;
  color: rgba(0,0,0,0.55);
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.search-filters-toggle .hint {
  font-size: 12px;
  color: rgba(0,0,0,0.40);
  line-height: 1.2;
}

/* Grid */
.search-filters-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr)) auto;
  gap: 12px;
  align-items: end;
}

.filter-field { display: grid; gap: 6px; min-width: 0; }

.filter-label {
  font-size: 12px;
  font-weight: 700;
  color: rgba(0,0,0,0.60);
}

.filter-control {
  height: 46px;
  width: 100%;
  padding: 0 12px;
  border-radius: 8px;
  border: 1px solid rgba(0,0,0,0.10);
  background: #fff;
  color: rgba(0,0,0,0.88);
  font-size: 16px;
  transition: border-color .15s ease, box-shadow .15s ease;
}

.filter-control:focus {
  outline: none;
  border-color: rgba(0,0,0,0.20);
  box-shadow: 0 0 0 3px rgba(0,0,0,0.06);
}

/* Segmented control (pill tabs) */
.segmented {
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: 1fr;
  align-items: stretch;
  width: 100%;
  height: 46px;

  padding: 4px;
  border-radius: 12px;
  border: 1px solid rgba(0,0,0,0.10);
  background: rgba(0,0,0,0.04);
  overflow: hidden;
}

.segmented input[type="radio"] {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.segmented label {
  display: inline-flex;
  align-items: center;
  justify-content: center;

  border-radius: 10px;
  cursor: pointer;
  user-select: none;

  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.2px;

  color: rgba(0,0,0,0.62);
  transition: background .15s ease, box-shadow .15s ease, color .15s ease, transform .15s ease;
}

/* ACTIVE — stronger pink but still below CTA */
.segmented input:checked + label {
  background: linear-gradient(
    180deg,
    rgba(236,72,153,0.18),
    rgba(236,72,153,0.10)
  );

  color: #9d174d;

  box-shadow:
    0 1px 0 rgba(236,72,153,0.30),
    0 6px 14px rgba(236,72,153,0.25),
    inset 0 0 0 1px rgba(236,72,153,0.45);
}

.segmented label:active { transform: translateY(0.5px); }

.segmented input:focus-visible + label {
  outline: none;
  box-shadow:
    0 0 0 3px rgba(236,72,153,0.20),
    0 6px 14px rgba(236,72,153,0.25);
}

/* -------- Age range (46px tall like other controls) -------- */
.age-field { min-width: 0; }

.age-labelrow{
  display: inline-flex;
  align-items: baseline;
  gap: 8px;
}

.age-value-inline{
  font-size: 12px;
  font-weight: 800;
  color: rgba(0,0,0,0.55);
}

.age-range {
  height: 46px;               /* same as other controls */
  border-radius: 12px;
  border: 1px solid rgba(0,0,0,0.10);
  background: #fff;
  padding: 0 12px;
  display: flex;
  align-items: center;        /* slider centered vertically */
}

/* Slider area centered */
.range-wrap {
  position: relative;
  width: 100%;
  height: 18px;
}

/* base track */
.range-track {
  position: absolute;
  left: 0;
  right: 0;
  top: 50%;
  height: 6px;
  transform: translateY(-50%);
  border-radius: 999px;
  background: rgba(0,0,0,0.10);
}

/* filled part */
.range-fill {
  position: absolute;
  top: 50%;
  height: 6px;
  transform: translateY(-50%);
  border-radius: 999px;
  background: rgba(236,72,153,0.38);
  box-shadow: inset 0 0 0 1px rgba(236,72,153,0.35);
}

/* the two sliders stacked */
.range {
  position: absolute;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  margin: 0;
  width: 100%;
  height: 18px;
  background: transparent;
  pointer-events: none;
  -webkit-appearance: none;
  appearance: none;
}

.range::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  pointer-events: auto;
  width: 18px;
  height: 18px;
  border-radius: 999px;
  background: #fff;
  border: 1px solid rgba(0,0,0,0.18);
  box-shadow:
    0 6px 14px rgba(0,0,0,0.12),
    0 0 0 3px rgba(236,72,153,0.14);
  cursor: pointer;
}

.range::-moz-range-thumb {
  pointer-events: auto;
  width: 18px;
  height: 18px;
  border-radius: 999px;
  background: #fff;
  border: 1px solid rgba(0,0,0,0.18);
  box-shadow:
    0 6px 14px rgba(0,0,0,0.12),
    0 0 0 3px rgba(236,72,153,0.14);
  cursor: pointer;
}

.range::-webkit-slider-runnable-track { height: 18px; background: transparent; }
.range::-moz-range-track { height: 18px; background: transparent; }

/* Buttons */
.filter-actions {
  display: inline-flex;
  gap: 10px;
  align-items: center;
}

.search-btn,
.reset-btn {
  height: 46px;
  padding: 0 18px;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

.reset-btn {
  text-decoration: none;
  color: #000;
  background: #e0e0e0;
  border: none;
}

/* Mobile */
@media (max-width: 560px) {
  .search-filters-card { padding: 12px; }
  .search-filters-mobilehead { display: flex; }
  .search-filters-grid { grid-template-columns: 1fr; gap: 10px; }

  .search-filters-card.is-collapsed .search-filters-body { display: none; }
  .search-filters-card.is-collapsed .search-filters-mobilehead { margin-bottom: 0; }

  .filter-actions { width: 100%; flex-direction: column; }
  .filter-actions .search-btn,
  .filter-actions .reset-btn { width: 100%; }
}
</style>

<form method="GET" action="<?php echo SITE_URL; ?>/search" class="search-filters">
  <div class="search-filters-card is-collapsed" id="searchFiltersCard">

    <!-- MOBILE ONLY -->
    <div class="search-filters-mobilehead">
      <div class="search-filters-toggle" id="searchFiltersToggle" aria-expanded="false">
        <div class="title">筛选条件</div>
        <div class="summary" id="searchFiltersSummary"><?php echo htmlspecialchars($summaryText); ?></div>
        <div class="hint" id="searchFiltersHint">点这里展开筛选</div>
      </div>
    </div>

    <div class="search-filters-body" id="searchFiltersBody">
      <div class="search-filters-grid">

        <label class="filter-field">
          <span class="filter-label">性别</span>
          <div class="segmented" role="tablist" aria-label="性别筛选" id="filterGender">
            <input type="radio" name="gender" id="gender_all" value="all" <?php echo $gender==='all'?'checked':''; ?>>
            <label for="gender_all">全部</label>

            <input type="radio" name="gender" id="gender_m" value="m" <?php echo $gender==='m'?'checked':''; ?>>
            <label for="gender_m">男</label>

            <input type="radio" name="gender" id="gender_f" value="f" <?php echo $gender==='f'?'checked':''; ?>>
            <label for="gender_f">女</label>
          </div>
        </label>

        <label class="filter-field">
          <span class="filter-label">照片</span>
          <div class="segmented" role="tablist" aria-label="照片筛选" id="filterPhoto">
            <input type="radio" name="photo" id="photo_any" value="any" <?php echo $photo==='any'?'checked':''; ?>>
            <label for="photo_any">不限</label>

            <input type="radio" name="photo" id="photo_with" value="with" <?php echo $photo==='with'?'checked':''; ?>>
            <label for="photo_with">有照片</label>

            <input type="radio" name="photo" id="photo_without" value="without" <?php echo $photo==='without'?'checked':''; ?>>
            <label for="photo_without">无照片</label>
          </div>
        </label>

        <!-- Age range -->
        <div class="filter-field age-field">
          <div class="age-labelrow">
            <span class="filter-label">年龄范围</span>
            <span class="age-value-inline" id="ageInlineText"><?php echo (int)$minAge; ?> - <?php echo (int)$maxAge; ?> 岁</span>
          </div>

          <div class="age-range" id="ageRangeRoot">
            <div class="range-wrap">
              <div class="range-track"></div>
              <div class="range-fill" id="ageRangeFill" style="left:0%; right:0%;"></div>

              <input class="range" type="range" id="ageMinRange" min="18" max="99" step="1"
                     value="<?php echo (int)$minAge; ?>" aria-label="最小年龄">
              <input class="range" type="range" id="ageMaxRange" min="18" max="99" step="1"
                     value="<?php echo (int)$maxAge; ?>" aria-label="最大年龄">
            </div>

            <!-- Hidden fields submit as minAge/maxAge -->
            <input type="hidden" name="minAge" id="filterMinAge" value="<?php echo (int)$minAge; ?>">
            <input type="hidden" name="maxAge" id="filterMaxAge" value="<?php echo (int)$maxAge; ?>">
          </div>
        </div>

        <div class="filter-actions">
          <button type="submit" class="btn submit-btn search-btn">搜索</button>
          <a href="<?php echo $resetUrl; ?>" class="btn reset-btn">重置</a>
        </div>

      </div>
    </div>
  </div>
</form>

<script>
(function () {
  var mq = window.matchMedia('(max-width: 560px)');
  var card = document.getElementById('searchFiltersCard');
  var toggle = document.getElementById('searchFiltersToggle');
  var hint = document.getElementById('searchFiltersHint');
  var summary = document.getElementById('searchFiltersSummary');

  var genderWrap = document.getElementById('filterGender');
  var photoWrap  = document.getElementById('filterPhoto');

  // Age range elements
  var ageMinRange = document.getElementById('ageMinRange');
  var ageMaxRange = document.getElementById('ageMaxRange');
  var ageFill = document.getElementById('ageRangeFill');
  var minHidden = document.getElementById('filterMinAge');
  var maxHidden = document.getElementById('filterMaxAge');
  var ageInlineText = document.getElementById('ageInlineText');

  if (!card) return;

  function labelGender(v) {
    if (v === 'm') return '男';
    if (v === 'f') return '女';
    return '性别不限';
  }

  function labelPhoto(v) {
    if (v === 'with') return '有照片';
    if (v === 'without') return '无照片';
    return '图片不限';
  }

  function getCheckedValue(name, fallback) {
    var el = document.querySelector('input[name="' + name + '"]:checked');
    return el ? el.value : fallback;
  }

  function clamp(n, lo, hi) {
    n = Number(n);
    if (Number.isNaN(n)) return lo;
    return Math.max(lo, Math.min(hi, n));
  }

  function updateAgeUI() {
    if (!ageMinRange || !ageMaxRange) return;

    var lo = Number(ageMinRange.min || 18);
    var hi = Number(ageMinRange.max || 99);

    var minV = clamp(ageMinRange.value, lo, hi);
    var maxV = clamp(ageMaxRange.value, lo, hi);

    if (minV > maxV) {
      if (document.activeElement === ageMinRange) maxV = minV;
      else minV = maxV;
    }

    ageMinRange.value = String(minV);
    ageMaxRange.value = String(maxV);

    if (minHidden) minHidden.value = String(minV);
    if (maxHidden) maxHidden.value = String(maxV);

    if (ageInlineText) ageInlineText.textContent = minV + ' - ' + maxV + ' 岁';

    if (ageFill) {
      var minPct = ((minV - lo) / (hi - lo)) * 100;
      var maxPct = ((maxV - lo) / (hi - lo)) * 100;
      ageFill.style.left = minPct + '%';
      ageFill.style.right = (100 - maxPct) + '%';
    }
  }

  function updateSummary() {
    if (!summary) return;

    var g = getCheckedValue('gender', 'all');
    var p = getCheckedValue('photo', 'any');

    var minA = minHidden ? (minHidden.value || '18') : '18';
    var maxA = maxHidden ? (maxHidden.value || '60') : '60';

    summary.textContent =
      labelGender(g) + ' | ' + labelPhoto(p) + ' | ' + minA + ' - ' + maxA + ' 岁';
  }

  function setCollapsed(collapsed) {
    card.classList.toggle('is-collapsed', collapsed);
    if (toggle) toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    if (hint) hint.textContent = collapsed ? '点这里展开筛选' : '点这里收起筛选';
  }

  function syncByViewport() {
    if (mq.matches) {
      setCollapsed(true);
      updateSummary();
    } else {
      setCollapsed(false);
    }
  }

  if (toggle) {
    toggle.addEventListener('click', function () {
      if (!mq.matches) return;
      setCollapsed(!card.classList.contains('is-collapsed'));
    });
  }

  function bindRadioGroup(container) {
    if (!container) return;
    var radios = container.querySelectorAll('input[type="radio"]');
    radios.forEach(function (r) {
      r.addEventListener('change', updateSummary);
    });
  }

  bindRadioGroup(genderWrap);
  bindRadioGroup(photoWrap);

  if (ageMinRange) {
    ageMinRange.addEventListener('input', function () {
      updateAgeUI();
      updateSummary();
    });
    ageMinRange.addEventListener('change', function () {
      updateAgeUI();
      updateSummary();
    });
  }
  if (ageMaxRange) {
    ageMaxRange.addEventListener('input', function () {
      updateAgeUI();
      updateSummary();
    });
    ageMaxRange.addEventListener('change', function () {
      updateAgeUI();
      updateSummary();
    });
  }

  updateAgeUI();

  syncByViewport();
  if (mq.addEventListener) mq.addEventListener('change', syncByViewport);
  else mq.addListener(syncByViewport);
})();
</script>
