# Technical Specification & Remediation Guide: Andri 3D Character & Apparel Assets

*Document Version:* 1.1.0  
*Target File:* `c:\laragon\www\tnm\marketplace\assets\models\andri_fullbody_rigged.blend`  
*Exported Asset:* `c:\laragon\www\tnm\marketplace\assets\models\andri_fullbody_rigged.glb`  
*Target Web Viewport:* `http://localhost/tnm/fitting-room.php`

---

## 1. Executive Summary & Visual Audit (Reference to Screenshots)

Based on direct visual inspection in the WebGL fitting room and coordinate inspection in Blender, the 3D model suffers from a critical architectural defect: **"Frankenstein Slicing"**. 

The character is not a continuous human body. Instead, it was assembled from isolated polygonal chunks sliced like salami, leaving visible gaps, floating stumps, and missing anatomy whenever clothing is viewed or swapped:

1. **Floating Arms in T-Shirt Mode (Screenshot 1):**
   - The T-shirt sleeve opening is wide and loose.
   - The arm (`Andri_Arms`) is sliced off flat at Z = 1.441m (upper bicep) with an open boundary ring.
   - There is an empty air gap between the sleeve and the arm; the arm stump hovers in mid-air in an A-pose completely detached from the torso.
2. **Severed Forearm Stumps in Hoodie Mode (Screenshot 2):**
   - The object `Andri_Hands` is not just hands; it extends up the forearm to Z = 1.267m.
   - When the hoodie is worn, this severed forearm section collides with and pokes outside the sleeve cuff, presenting a jarring, floating tan cylinder beside the wrist.
3. **Completely Missing Neck (Screenshots 1 & 2):**
   - The head (`Andri_Head`) terminates abruptly at the jawline (Z = 1.540m).
   - The T-shirt/hoodie collar sits lower at Z = 1.460m - 1.500m.
   - There are zero neck polygons; the head floats above the collar like a decapitated bust.
4. **Floating Shoes & Missing Ankles (Screenshots 1 & 2):**
   - The bottom cuff of `Andri_Jeans` ends at Z = 0.087m - 0.199m.
   - The shoes (`Andri_Shoes`, only 72 polygons) have a maximum height of Z = 0.078m.
   - There is an empty void between the pant cuff and the shoes: no ankles, no socks, no lower leg geometry. The shoes hover detached beneath the pants.
5. **Planar Board Artifact Behind Head:**
   - A rigid, flat, vertical polygonal plane stands upright directly behind the nape of the neck and occipital bone (leftover uncleaned photogrammetry scan backdrop).

---

## 2. Quantitative Anatomical Coordinate Breakdown

| Body / Apparel Mesh | Vertex Count | Z-Bounds (Height) | X-Bounds (Width) | Critical Structural Defect |
| :--- | :--- | :--- | :--- | :--- |
| **`Andri_Head`** | 3,010 | 1.540m to 1.755m | -0.084m to 0.084m | Sliced at jaw (Z = 1.540m). No neck connecting to torso. |
| **`Andri_Arms`** | 662 | 0.971m to 1.441m | -0.661m to 0.661m | Sliced at upper bicep (Z = 1.441m). Floating in space under T-shirt. |
| **`Andri_Hands`** | 586 | 0.971m to 1.267m | -0.661m to 0.661m | Includes forearm up to Z = 1.267m. Sliced stump pokes out of hoodie cuff. |
| **`Andri_TShirt`** | 11,716 | 1.005m to 1.605m | -0.297m to 0.297m | Sleeves end at X = 0.297m. Large gap to `Andri_Arms` (X = 0.239m). |
| **`Andri_Hoodie`** | 59,269 | 0.998m to 1.661m | -0.573m to 0.573m | Sleeve cuff collides with `Andri_Hands` severed forearm. |
| **`Andri_Jeans`** | 23,770 | 0.086m to 1.057m | -0.206m to 0.209m | Bottom hem ends at Z = 0.087m. Hollow opening inside. |
| **`Andri_Shoes`** | 128 (72 polys) | 0.000m to 0.078m | -0.161m to 0.161m | Max Z = 0.078m. 1cm - 12cm void gap between jeans and shoes. No ankles. |

---

## 3. Permanent Remediation Roadmap (Step-by-Step)

The ultimate solution is to eliminate the sliced "Frankenstein" approach and implement proper anatomical continuity.

### Step 1: Model the Missing Neck
1. Select `Andri_Head`, enter Edit Mode (`Tab`).
2. Select the boundary edge loop at the lower jaw/cranium (Z = 1.540m).
3. Extrude downward (`E` -> `Z`) in 4 rings down to Z = 1.440m (into the chest cavity).
4. Assign these new polygons to material `M_Andri_Skin_Master`.
5. Weight paint the new neck rings:
   - Upper ring: 80% `spine.005` (head), 20% `spine.004` (neck).
   - Middle rings: 100% `spine.004` (neck).
   - Base ring: 50% `spine.004`, 50% `spine.003` (upper chest).

### Step 2: Bridge Shoulders to Arms (Eliminate Floating Arms in T-Shirt)
1. Select `Andri_Arms`, enter Edit Mode.
2. Select the top open boundary edge loop at Z = 1.441m.
3. Extrude inward toward the body midline:
   - Loop 1: Deltoid cap (X = +/-0.18m, Z = 1.48m).
   - Loop 2: Clavicle/pectoral base (X = +/-0.10m, Z = 1.50m).
4. This ensures the arm mesh extends deep into the torso, so when wearing short-sleeved shirts, looking into the sleeve opening reveals continuous skin and deltoid, not an empty void.
5. Weight paint the new shoulder vertices to `shoulder.L`/`shoulder.R`, `upper_arm.L`/`upper_arm.R`, and `spine.003`.

### Step 3: Fix Forearm/Hand Stump for Hoodie Mode
1. Inspect `Andri_Hands`:
   - Either trim `Andri_Hands` so it strictly contains the wrist and fingers (Z below 1.08m), OR
   - Ensure the hoodie sleeve cuff has an inner lining/rim and the hand mesh cleanly nests inside the cuff without protruding sideways.
2. Align the wrist boundary of `Andri_Hands` with the wrist bone (`hand.L` / `hand.R`).

### Step 4: Model Ankles and Socks (Connect Jeans to Shoes)
1. In Edit Mode on `Andri_Shoes` or `Andri_Jeans`:
   - Create an anatomical ankle/sock cylinder (`Andri_Ankles` or integrated into `Andri_Shoes`/`Andri_Jeans`) spanning from Z = 0.05m to Z = 0.22m.
   - The lower rim attaches or sinks into the shoe opening (Z = 0.06m).
   - The upper rim extends high enough (Z = 0.22m) into the pant leg so that during walking, posing, or camera rotation, the pants never reveal an empty void above the shoes.
2. Assign weights to `foot.L`/`foot.R` and `shin.L`/`shin.R`.

### Step 5: Delete Stray Planar Board Behind Head
1. Isolate `Andri_Head` and `Andri_Hair`.
2. Delete all flat coplanar scan artifact faces in the region Y > 0.04m, Z between 1.45m and 1.65m.

### Step 6: Mirror UV on Back of T-Shirt
1. Select rear-facing faces on `Andri_TShirt`.
2. Flip UV coordinates horizontally (`S` -> `X` -> `-1`) to fix mirrored decal text.

### Step 7: Clean Armatures and Re-Export GLB
1. Delete unused armatures: `metarig`, `metarig.001`, `metarig.002`, `metarig.003`, `metarig.004`.
2. Apply transforms on all character meshes.
3. Export to `c:\laragon\www\tnm\marketplace\assets\models\andri_fullbody_rigged.glb`.
4. Refresh `http://localhost/tnm/fitting-room.php` and test in both T-shirt and Hoodie modes across 360-degree rotation.
