<?php
/**
 * Vector SVG Asset Generator for TNM Marketplace
 * Creates crisp, high-end vector models, preset graphics, and product mockups.
 */

$modelsDir = __DIR__ . '/img/models';
$presetsDir = __DIR__ . '/img/presets';
$productsDir = __DIR__ . '/img/products';

@mkdir($modelsDir, 0755, true);
@mkdir($presetsDir, 0755, true);
@mkdir($productsDir, 0755, true);

// 1. T-Shirt Front
$tshirtFront = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 700" width="100%" height="100%">
  <defs>
    <filter id="fabricTexture" x="0%" y="0%" width="100%" height="100%">
      <feTurbulence type="fractalNoise" baseFrequency="0.04" numOctaves="3" result="noise" />
      <feColorMatrix type="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 0.04 0" in="noise" result="softNoise" />
      <feBlend in="SourceGraphic" in2="softNoise" mode="multiply" />
    </filter>
    <linearGradient id="foldShadow" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#000000" stop-opacity="0.32" />
      <stop offset="12%" stop-color="#000000" stop-opacity="0.04" />
      <stop offset="88%" stop-color="#000000" stop-opacity="0.04" />
      <stop offset="100%" stop-color="#000000" stop-opacity="0.32" />
    </linearGradient>
    <linearGradient id="bodyCrease" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#ffffff" stop-opacity="0.12" />
      <stop offset="50%" stop-color="#000000" stop-opacity="0.0" />
      <stop offset="100%" stop-color="#000000" stop-opacity="0.25" />
    </linearGradient>
  </defs>

  <!-- Base Shirt Silhouette -->
  <path id="garmentBody" class="garment-color-fill"
        d="M 215 85 Q 300 135 385 85 L 530 145 L 475 275 L 420 230 L 425 625 Q 300 645 175 625 L 180 230 L 125 275 L 70 145 Z"
        fill="#141414" stroke="#000000" stroke-width="2" stroke-linejoin="round" />

  <!-- Shading and Folds Overlay -->
  <path d="M 215 85 Q 300 135 385 85 L 530 145 L 475 275 L 420 230 L 425 625 Q 300 645 175 625 L 180 230 L 125 275 L 70 145 Z"
        fill="url(#foldShadow)" style="mix-blend-mode: multiply;" />

  <path d="M 215 85 Q 300 135 385 85 L 530 145 L 475 275 L 420 230 L 425 625 Q 300 645 175 625 L 180 230 L 125 275 L 70 145 Z"
        fill="url(#bodyCrease)" style="mix-blend-mode: overlay;" />

  <!-- Ribbed Crew Collar -->
  <path d="M 215 85 Q 300 135 385 85 Q 300 152 215 85 Z" fill="#0d0d0d" stroke="#000000" stroke-width="1.5" />
  <path d="M 218 87 Q 300 140 382 87" fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="1.5" stroke-dasharray="3,2" />

  <!-- Sleeve Seams -->
  <path d="M 420 230 Q 440 180 435 110" fill="none" stroke="rgba(0,0,0,0.4)" stroke-width="2" />
  <path d="M 180 230 Q 160 180 165 110" fill="none" stroke="rgba(0,0,0,0.4)" stroke-width="2" />

  <!-- Sleeve Hem Stitches -->
  <path d="M 477 265 L 525 152" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="1" stroke-dasharray="4,2" />
  <path d="M 123 265 L 75 152" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="1" stroke-dasharray="4,2" />

  <!-- Bottom Hem Stitch -->
  <path d="M 180 615 Q 300 635 420 615" fill="none" stroke="rgba(255,255,255,0.14)" stroke-width="1.5" stroke-dasharray="4,2" />
</svg>
SVG;
file_put_contents("$modelsDir/tshirt-front.svg", $tshirtFront);

// 2. T-Shirt Back
$tshirtBack = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 700" width="100%" height="100%">
  <defs>
    <linearGradient id="foldShadowBack" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#000000" stop-opacity="0.32" />
      <stop offset="12%" stop-color="#000000" stop-opacity="0.04" />
      <stop offset="88%" stop-color="#000000" stop-opacity="0.04" />
      <stop offset="100%" stop-color="#000000" stop-opacity="0.32" />
    </linearGradient>
  </defs>

  <path id="garmentBody" class="garment-color-fill"
        d="M 215 85 Q 300 95 385 85 L 530 145 L 475 275 L 420 230 L 425 625 Q 300 645 175 625 L 180 230 L 125 275 L 70 145 Z"
        fill="#141414" stroke="#000000" stroke-width="2" stroke-linejoin="round" />

  <path d="M 215 85 Q 300 95 385 85 L 530 145 L 475 275 L 420 230 L 425 625 Q 300 645 175 625 L 180 230 L 125 275 L 70 145 Z"
        fill="url(#foldShadowBack)" style="mix-blend-mode: multiply;" />

  <!-- High Back Collar -->
  <path d="M 215 85 Q 300 95 385 85 Q 300 108 215 85 Z" fill="#0d0d0d" stroke="#000000" stroke-width="1.5" />
  <!-- Back neck half-moon yoke -->
  <path d="M 240 100 Q 300 135 360 100" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="1.5" stroke-dasharray="3,2" />
  
  <path d="M 420 230 Q 440 180 435 110" fill="none" stroke="rgba(0,0,0,0.4)" stroke-width="2" />
  <path d="M 180 230 Q 160 180 165 110" fill="none" stroke="rgba(0,0,0,0.4)" stroke-width="2" />
  <path d="M 180 615 Q 300 635 420 615" fill="none" stroke="rgba(255,255,255,0.14)" stroke-width="1.5" stroke-dasharray="4,2" />
</svg>
SVG;
file_put_contents("$modelsDir/tshirt-back.svg", $tshirtBack);

// 3. Hoodie Front
$hoodieFront = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 700" width="100%" height="100%">
  <defs>
    <linearGradient id="hoodieShade" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#000000" stop-opacity="0.35" />
      <stop offset="15%" stop-color="#000000" stop-opacity="0.05" />
      <stop offset="85%" stop-color="#000000" stop-opacity="0.05" />
      <stop offset="100%" stop-color="#000000" stop-opacity="0.35" />
    </linearGradient>
  </defs>

  <!-- Main Torso & Sleeves -->
  <path id="garmentBody" class="garment-color-fill"
        d="M 210 115 L 140 125 L 45 320 L 105 345 L 165 240 L 160 590 L 440 590 L 435 240 L 495 345 L 555 320 L 460 125 L 390 115 Z"
        fill="#141414" stroke="#000000" stroke-width="2" stroke-linejoin="round" />

  <path d="M 210 115 L 140 125 L 45 320 L 105 345 L 165 240 L 160 590 L 440 590 L 435 240 L 495 345 L 555 320 L 460 125 L 390 115 Z"
        fill="url(#hoodieShade)" style="mix-blend-mode: multiply;" />

  <!-- Kangaroo Pocket -->
  <path d="M 200 420 L 400 420 L 420 540 L 180 540 Z" fill="#0d0d0d" stroke="rgba(255,255,255,0.12)" stroke-width="1.5" />
  <path d="M 200 420 L 180 540" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="2" />
  <path d="M 400 420 L 420 540" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="2" />

  <!-- Hood Structure -->
  <path d="M 210 115 Q 190 40 260 25 Q 300 20 340 25 Q 410 40 390 115 Q 300 135 210 115 Z" fill="#181818" stroke="#000000" stroke-width="2" />
  <!-- Inner hood shadow -->
  <path d="M 235 110 Q 300 70 365 110 Q 300 130 235 110 Z" fill="#0a0a0a" />

  <!-- Drawstrings -->
  <path d="M 270 125 Q 265 210 260 270" fill="none" stroke="#ECE8DD" stroke-width="3" stroke-linecap="round" />
  <rect x="257" y="270" width="6" height="12" rx="2" fill="#D63B29" />
  <path d="M 330 125 Q 335 210 340 270" fill="none" stroke="#ECE8DD" stroke-width="3" stroke-linecap="round" />
  <rect x="337" y="270" width="6" height="12" rx="2" fill="#D63B29" />

  <!-- Ribbed Bottom Hem & Cuffs -->
  <rect x="160" y="590" width="280" height="35" rx="3" fill="#0d0d0d" stroke="#000000" stroke-width="1.5" />
  <rect x="45" y="325" width="60" height="25" rx="2" transform="rotate(22 45 325)" fill="#0d0d0d" stroke="#000000" stroke-width="1" />
  <rect x="495" y="345" width="60" height="25" rx="2" transform="rotate(-22 495 345)" fill="#0d0d0d" stroke="#000000" stroke-width="1" />
</svg>
SVG;
file_put_contents("$modelsDir/hoodie-front.svg", $hoodieFront);

// 4. Hoodie Back
$hoodieBack = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 700" width="100%" height="100%">
  <defs>
    <linearGradient id="hoodieShadeBack" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#000000" stop-opacity="0.35" />
      <stop offset="15%" stop-color="#000000" stop-opacity="0.05" />
      <stop offset="85%" stop-color="#000000" stop-opacity="0.05" />
      <stop offset="100%" stop-color="#000000" stop-opacity="0.35" />
    </linearGradient>
  </defs>

  <path id="garmentBody" class="garment-color-fill"
        d="M 210 115 L 140 125 L 45 320 L 105 345 L 165 240 L 160 590 L 440 590 L 435 240 L 495 345 L 555 320 L 460 125 L 390 115 Z"
        fill="#141414" stroke="#000000" stroke-width="2" stroke-linejoin="round" />

  <path d="M 210 115 L 140 125 L 45 320 L 105 345 L 165 240 L 160 590 L 440 590 L 435 240 L 495 345 L 555 320 L 460 125 L 390 115 Z"
        fill="url(#hoodieShadeBack)" style="mix-blend-mode: multiply;" />

  <!-- Draped Hood Back -->
  <path d="M 210 110 Q 180 50 300 45 Q 420 50 390 110 Q 300 185 210 110 Z" fill="#181818" stroke="#000000" stroke-width="2" />
  <path d="M 230 115 Q 300 165 370 115" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" />

  <rect x="160" y="590" width="280" height="35" rx="3" fill="#0d0d0d" stroke="#000000" stroke-width="1.5" />
</svg>
SVG;
file_put_contents("$modelsDir/hoodie-back.svg", $hoodieBack);

// 5. Oversize T-Shirt Front
$oversizeFront = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 700" width="100%" height="100%">
  <defs>
    <linearGradient id="oversizeShade" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#000000" stop-opacity="0.32" />
      <stop offset="12%" stop-color="#000000" stop-opacity="0.04" />
      <stop offset="88%" stop-color="#000000" stop-opacity="0.04" />
      <stop offset="100%" stop-color="#000000" stop-opacity="0.32" />
    </linearGradient>
  </defs>

  <!-- Boxy Drop-Shoulder Silhouette -->
  <path id="garmentBody" class="garment-color-fill"
        d="M 215 85 Q 300 135 385 85 L 555 160 L 510 320 L 440 270 L 445 640 Q 300 655 155 640 L 160 270 L 90 320 L 45 160 Z"
        fill="#141414" stroke="#000000" stroke-width="2" stroke-linejoin="round" />

  <path d="M 215 85 Q 300 135 385 85 L 555 160 L 510 320 L 440 270 L 445 640 Q 300 655 155 640 L 160 270 L 90 320 L 45 160 Z"
        fill="url(#oversizeShade)" style="mix-blend-mode: multiply;" />

  <!-- Thick Ribbed Collar -->
  <path d="M 215 85 Q 300 135 385 85 Q 300 156 215 85 Z" fill="#0d0d0d" stroke="#000000" stroke-width="1.8" />
  <!-- Drop shoulder stitch lines -->
  <path d="M 470 125 L 440 270" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="1.5" stroke-dasharray="3,2" />
  <path d="M 130 125 L 160 270" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="1.5" stroke-dasharray="3,2" />
</svg>
SVG;
file_put_contents("$modelsDir/oversize-front.svg", $oversizeFront);

// 6. Oversize T-Shirt Back
$oversizeBack = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 700" width="100%" height="100%">
  <path id="garmentBody" class="garment-color-fill"
        d="M 215 85 Q 300 95 385 85 L 555 160 L 510 320 L 440 270 L 445 640 Q 300 655 155 640 L 160 270 L 90 320 L 45 160 Z"
        fill="#141414" stroke="#000000" stroke-width="2" stroke-linejoin="round" />
  <path d="M 215 85 Q 300 95 385 85 Q 300 110 215 85 Z" fill="#0d0d0d" stroke="#000000" stroke-width="1.8" />
</svg>
SVG;
file_put_contents("$modelsDir/oversize-back.svg", $oversizeBack);

// 7. Preset Designs (Artworks ready to place on garment)
$presetWordmark = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 100">
  <text x="15" y="78" font-family="'Archivo Black', 'Arial Black', Impact, sans-serif" font-size="78" font-weight="900" fill="#ECE8DD" letter-spacing="-2">TNM</text>
  <rect x="228" y="52" width="16" height="16" fill="#D63B29" />
</svg>
SVG;
file_put_contents("$presetsDir/preset-logo-tnm.svg", $presetWordmark);

$presetBadge = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240">
  <circle cx="120" cy="120" r="110" fill="none" stroke="#D63B29" stroke-width="5" stroke-dasharray="6,4" />
  <circle cx="120" cy="120" r="98" fill="#1A1712" stroke="#ECE8DD" stroke-width="3" />
  <text x="120" y="90" font-family="'Archivo Black', sans-serif" font-size="28" font-weight="900" fill="#ECE8DD" text-anchor="middle">TEMAN</text>
  <text x="120" y="125" font-family="'Archivo Black', sans-serif" font-size="26" font-weight="900" fill="#D63B29" text-anchor="middle">NGOBROL</text>
  <text x="120" y="158" font-family="'Archivo Black', sans-serif" font-size="28" font-weight="900" fill="#ECE8DD" text-anchor="middle">MALAM</text>
  <text x="120" y="186" font-family="sans-serif" font-size="11" font-weight="700" fill="#A8A294" text-anchor="middle" letter-spacing="3">EST. 2012</text>
</svg>
SVG;
file_put_contents("$presetsDir/preset-badge-radio.svg", $presetBadge);

$presetCerita = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240">
  <rect width="240" height="240" rx="28" fill="#1C1826" stroke="#9065B0" stroke-width="3" />
  <path d="M120 40 C75 40 40 75 40 120 C40 155 62 185 95 195 L95 210 L145 210 L145 195 C178 185 200 155 200 120 C200 75 165 40 120 40 Z" fill="#2E2442" />
  <circle cx="95" cy="115" r="14" fill="#D63B29" />
  <circle cx="145" cy="115" r="14" fill="#D63B29" />
  <path d="M120 135 L112 155 L128 155 Z" fill="#ECE8DD" />
  <text x="120" y="228" font-family="'Archivo Black', sans-serif" font-size="19" font-weight="900" fill="#D63B29" text-anchor="middle">#CERITASEREM</text>
</svg>
SVG;
file_put_contents("$presetsDir/preset-ceritaserem.svg", $presetCerita);

$presetMalming = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240">
  <rect width="240" height="240" rx="28" fill="#241E15" stroke="#E09548" stroke-width="3" />
  <!-- Cassette body -->
  <rect x="35" y="60" width="170" height="105" rx="10" fill="#1A1712" stroke="#E09548" stroke-width="2.5" />
  <rect x="65" y="85" width="110" height="55" rx="6" fill="#F4EDE0" />
  <circle cx="95" cy="112" r="14" fill="#1A1712" stroke="#E09548" stroke-width="2" />
  <circle cx="145" cy="112" r="14" fill="#1A1712" stroke="#E09548" stroke-width="2" />
  <text x="120" y="195" font-family="'Archivo Black', sans-serif" font-size="18" font-weight="900" fill="#E09548" text-anchor="middle">#MALAMMINGGU</text>
</svg>
SVG;
file_put_contents("$presetsDir/preset-malamminggu.svg", $presetMalming);

$presetPerkara = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240">
  <rect width="240" height="240" rx="28" fill="#221210" stroke="#D63B29" stroke-width="3" />
  <circle cx="120" cy="110" r="65" fill="none" stroke="#D63B29" stroke-width="6" stroke-dasharray="10,6" />
  <text x="120" y="105" font-family="'Archivo Black', sans-serif" font-size="34" font-weight="900" fill="#D63B29" text-anchor="middle">BEDAH</text>
  <text x="120" y="138" font-family="'Archivo Black', sans-serif" font-size="26" font-weight="900" fill="#ECE8DD" text-anchor="middle">PERKARA</text>
  <text x="120" y="215" font-family="'Archivo Black', sans-serif" font-size="18" font-weight="900" fill="#D63B29" text-anchor="middle">INDONESIA</text>
</svg>
SVG;
file_put_contents("$presetsDir/preset-perkara.svg", $presetPerkara);

$presetTypoCerita = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 80">
  <text x="140" y="34" font-family="'Archivo Black', sans-serif" font-size="20" font-weight="900" fill="#ECE8DD" text-anchor="middle" letter-spacing="1">CERITA MALAM INI</text>
  <text x="140" y="64" font-family="'Archivo Black', sans-serif" font-size="26" font-weight="900" fill="#D63B29" text-anchor="middle" letter-spacing="2">BIKIN MERINDING</text>
</svg>
SVG;
file_put_contents("$presetsDir/preset-typo-cerita.svg", $presetTypoCerita);

$presetTypoKisah = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 80">
  <text x="140" y="34" font-family="'Archivo Black', sans-serif" font-size="22" font-weight="900" fill="#D63B29" text-anchor="middle" letter-spacing="1">KISAH NYATA</text>
  <text x="140" y="62" font-family="'Archivo Black', sans-serif" font-size="18" font-weight="800" fill="#ECE8DD" text-anchor="middle" letter-spacing="2">BUKAN FIKTIF</text>
</svg>
SVG;
file_put_contents("$presetsDir/preset-typo-kisah.svg", $presetTypoKisah);

$presetEmblemRed = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160">
  <rect width="160" height="160" rx="32" fill="#D63B29" />
  <text x="80" y="105" font-family="'Archivo Black', sans-serif" font-size="64" font-weight="900" fill="#FFFFFF" text-anchor="middle">T</text>
</svg>
SVG;
file_put_contents("$presetsDir/preset-emblem-red.svg", $presetEmblemRed);

// 8. Generate Product Mockup SVGs for catalog display
// T-Shirt Custom
copy("$modelsDir/tshirt-front.svg", "$productsDir/custom-shirt.svg");
// Hoodie Custom
copy("$modelsDir/hoodie-front.svg", "$productsDir/custom-hoodie.svg");

// Other products mockups
$tshirtCeritaSerem = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 700">
  <use href="#garmentBody" />
  <image href="../models/tshirt-front.svg" width="600" height="700" />
  <g transform="translate(225, 200) scale(0.65)">
    $presetCerita
  </g>
</svg>
SVG;
file_put_contents("$productsDir/tshirt-ceritaserem.svg", $tshirtCeritaSerem);

$tshirtMalming = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 700">
  <image href="../models/tshirt-front.svg" width="600" height="700" />
  <g transform="translate(225, 200) scale(0.65)">
    $presetMalming
  </g>
</svg>
SVG;
file_put_contents("$productsDir/tshirt-malamminggu.svg", $tshirtMalming);

$tshirtPerkara = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 700">
  <image href="../models/tshirt-front.svg" width="600" height="700" />
  <g transform="translate(225, 200) scale(0.65)">
    $presetPerkara
  </g>
</svg>
SVG;
file_put_contents("$productsDir/tshirt-perkara.svg", $tshirtPerkara);

// Tumbler
$tumblerSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 500" width="100%" height="100%">
  <rect width="100%" height="100%" fill="#121212"/>
  <defs>
    <linearGradient id="metalSheen" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#2a2a2a" />
      <stop offset="35%" stop-color="#4a4a4a" />
      <stop offset="70%" stop-color="#1f1f1f" />
      <stop offset="100%" stop-color="#0a0a0a" />
    </linearGradient>
  </defs>
  <!-- Tumbler Body -->
  <path d="M 130 90 L 270 90 L 255 420 Q 200 435 145 420 Z" fill="url(#metalSheen)" stroke="#000000" stroke-width="2" />
  <!-- Cap -->
  <rect x="120" y="55" width="160" height="35" rx="6" fill="#141414" stroke="#D63B29" stroke-width="2" />
  <!-- TNM Engraving -->
  <text x="200" y="260" font-family="'Archivo Black', sans-serif" font-size="34" font-weight="900" fill="#ECE8DD" text-anchor="middle" letter-spacing="-1">TNM</text>
  <rect x="238" y="248" width="8" height="8" fill="#D63B29" />
  <text x="200" y="290" font-family="sans-serif" font-size="11" font-weight="700" fill="#857F72" text-anchor="middle" letter-spacing="2">MIDNIGHT 500ML</text>
</svg>
SVG;
file_put_contents("$productsDir/tumbler-tnm.svg", $tumblerSvg);

// Cap
$capSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 500" width="100%" height="100%">
  <rect width="100%" height="100%" fill="#121212"/>
  <!-- Crown Dome -->
  <path d="M 90 290 Q 95 140 200 135 Q 305 140 310 290 Z" fill="#181818" stroke="#000" stroke-width="2" />
  <!-- Visor / Brim -->
  <path d="M 60 290 Q 200 340 340 290 Q 200 375 60 290 Z" fill="#0d0d0d" stroke="#000" stroke-width="2" />
  <!-- 3D Embroidered TNM -->
  <rect x="165" y="195" width="70" height="35" rx="6" fill="#D63B29" />
  <text x="200" y="222" font-family="'Archivo Black', sans-serif" font-size="20" font-weight="900" fill="#FFFFFF" text-anchor="middle">TNM</text>
</svg>
SVG;
file_put_contents("$productsDir/cap-tnm.svg", $capSvg);

// Tote bag
$toteSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 500" width="100%" height="100%">
  <rect width="100%" height="100%" fill="#121212"/>
  <!-- Straps -->
  <path d="M 140 210 L 140 70 Q 200 45 260 70 L 260 210" fill="none" stroke="#2A241C" stroke-width="18" stroke-linecap="round" />
  <!-- Bag Body -->
  <rect x="90" y="190" width="220" height="260" rx="10" fill="#24201A" stroke="#3A332A" stroke-width="2" />
  <text x="200" y="310" font-family="'Archivo Black', sans-serif" font-size="32" font-weight="900" fill="#ECE8DD" text-anchor="middle">TNM</text>
  <rect x="238" y="298" width="8" height="8" fill="#D63B29" />
  <text x="200" y="340" font-family="sans-serif" font-size="12" font-weight="700" fill="#E09548" text-anchor="middle">TEMAN NGOBROL MALAM</text>
</svg>
SVG;
file_put_contents("$productsDir/totebag-tnm.svg", $toteSvg);

// Lanyard
$lanyardSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 500" width="100%" height="100%">
  <rect width="100%" height="100%" fill="#121212"/>
  <path d="M 150 40 Q 200 20 250 40 L 210 320 L 190 320 Z" fill="#D63B29" />
  <circle cx="200" cy="330" r="12" fill="#888888" />
  <!-- Cardholder -->
  <rect x="140" y="345" width="120" height="135" rx="10" fill="#1c1c1c" stroke="#333" stroke-width="2" />
  <rect x="155" y="365" width="90" height="60" rx="6" fill="#0d0d0d" />
  <text x="200" y="405" font-family="'Archivo Black', sans-serif" font-size="18" font-weight="900" fill="#ECE8DD" text-anchor="middle">TNM VIP</text>
</svg>
SVG;
file_put_contents("$productsDir/lanyard-tnm.svg", $lanyardSvg);

// Stickers
$stickersSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 500" width="100%" height="100%">
  <rect width="100%" height="100%" fill="#121212"/>
  <rect x="70" y="70" width="260" height="360" rx="16" fill="#1A1712" stroke="#D63B29" stroke-width="2" />
  <text x="200" y="115" font-family="'Archivo Black', sans-serif" font-size="22" font-weight="900" fill="#ECE8DD" text-anchor="middle">STICKER PACK</text>
  <rect x="95" y="145" width="95" height="95" rx="12" fill="#D63B29" />
  <text x="142" y="205" font-family="'Archivo Black', sans-serif" font-size="28" font-weight="900" fill="#FFF" text-anchor="middle">TNM</text>
  <rect x="210" y="145" width="95" height="95" rx="12" fill="#2E2442" stroke="#9065B0" stroke-width="2" />
  <text x="257" y="200" font-family="'Archivo Black', sans-serif" font-size="12" font-weight="900" fill="#D63B29" text-anchor="middle">#SEREM</text>
  <rect x="95" y="260" width="210" height="70" rx="10" fill="#241E15" stroke="#E09548" stroke-width="2" />
  <text x="200" y="302" font-family="'Archivo Black', sans-serif" font-size="18" font-weight="900" fill="#E09548" text-anchor="middle">#MALAMMINGGU</text>
  <text x="200" y="385" font-family="sans-serif" font-size="12" font-weight="700" fill="#A8A294" text-anchor="middle">12 PCS DIE-CUT HOLOGRAM</text>
</svg>
SVG;
file_put_contents("$productsDir/stickers-tnm.svg", $stickersSvg);

// Lighter
$lighterSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 500" width="100%" height="100%">
  <rect width="100%" height="100%" fill="#121212"/>
  <!-- Lighter Body -->
  <rect x="135" y="200" width="130" height="190" rx="12" fill="#1f1f1f" stroke="#000" stroke-width="2" />
  <!-- Lighter Cap -->
  <rect x="135" y="110" width="130" height="85" rx="12" fill="#1f1f1f" stroke="#000" stroke-width="2" />
  <line x1="135" y1="195" x2="265" y2="195" stroke="#000000" stroke-width="3" />
  <!-- Logo engraving -->
  <text x="200" y="295" font-family="'Archivo Black', sans-serif" font-size="32" font-weight="900" fill="#D63B29" text-anchor="middle">TNM</text>
  <text x="200" y="325" font-family="sans-serif" font-size="10" font-weight="800" fill="#888" text-anchor="middle" letter-spacing="2">MATTE BLACK</text>
</svg>
SVG;
file_put_contents("$productsDir/lighter-tnm.svg", $lighterSvg);

// Journal
$journalSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 500" width="100%" height="100%">
  <rect width="100%" height="100%" fill="#121212"/>
  <!-- Book Cover -->
  <rect x="100" y="70" width="215" height="360" rx="12" fill="#171513" stroke="#2e2b26" stroke-width="3" />
  <!-- Spine -->
  <path d="M 100 70 L 125 70 L 125 430 L 100 430 Z" fill="#0d0c0b" />
  <!-- Red Bookmark Ribbon -->
  <rect x="235" y="60" width="12" height="180" fill="#D63B29" />
  <!-- Embossed Title -->
  <text x="220" y="220" font-family="'Archivo Black', sans-serif" font-size="28" font-weight="900" fill="#ECE8DD" text-anchor="middle">CATATAN</text>
  <text x="220" y="255" font-family="'Archivo Black', sans-serif" font-size="24" font-weight="900" fill="#D63B29" text-anchor="middle">MALAM</text>
  <text x="220" y="285" font-family="sans-serif" font-size="11" font-weight="700" fill="#777" text-anchor="middle">TNM OFFICIAL NOTEBOOK</text>
</svg>
SVG;
file_put_contents("$productsDir/journal-tnm.svg", $journalSvg);

echo "All SVG models, presets, and mockups generated successfully.\n";
