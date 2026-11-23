# Technical Specification & Remediation Guide: Andri 3D Character & Apparel Assets

*Document Version:* 1.0.0  
*Target File:* `c:\laragon\www\tnm\marketplace\assets\models\andri_fullbody_rigged.blend`  
*Exported Asset:* `c:\laragon\www\tnm\marketplace\assets\models\andri_fullbody_rigged.glb`  
*Target Web Viewport:* `http://localhost/tnm/fitting-room.php`

---

## 1. Executive Summary & Audit Findings

Visual and geometric audit of the 3D model `andri_fullbody_rigged` reveals that while the head portrait, hair, and footwear textures are reasonably aligned, the underlying anatomical mesh assembly suffers from severe disconnection, missing anatomical components, scan artifacts, and file bloat.

### Summary of Observed Critical Defects:
1. **Disconnected and Floating Arms:**
   - The arms (`Andri_Arms`) are hollow, open polygonal cylinders hovering in mid-air in an A-pose.
   - They have no physical attachment or polygonal transition to the shoulders, clavicle, or torso.
   - When wearing the short-sleeved T-shirt, looking into the sleeve opening reveals empty void space with jagged open edges.
2. **Missing Neck Geometry:**
   - There is no cervical spine / neck mesh connecting the base of the skull/jaw (`Andri_Head`, minimum Z = 1.540m) to the upper torso/collar (Z ~ 1.450m - 1.500m).
   - The head floats directly above the shirt collar like a severed bust.
3. **Planar Board Artifact Behind the Head:**
   - A rigid, flat, vertical polygonal plane stands upright directly behind the nape of the neck and occipital bone.
   - This is an uncleaned backdrop plane from the original photogrammetry/3D scan that was never deleted.
4. **Mirrored Back UV Coordinates on T-Shirt:**
   - The rear decal UV mapping on `Andri_TShirt` is inverted along the X/U axis, causing text graphics (e.g., "TNM") to render backwards ("MИT").
5. **Project File Bloat (Dead Weight):**
   - The `.blend` file contains 5 unused duplicate armature objects (`metarig`, `metarig.001`, `metarig.002`, `metarig.003`, `metarig.004`), adding clutter and export risks.

---

## 2. Root Cause Analysis (Technical Breakdown)

### A. Anatomical Mesh Fragmentation
- **Cause:** The original 3D mesh was aggressively cut into disjointed parts (`Andri_Head`, `Andri_Arms`, `Andri_Hands`, `Andri_Jeans`, `Andri_Shoes`) under the assumption that clothing would 100% occlude the body underneath.
- **Flaw:** Because real human clothing moves, has open apertures (collars, short sleeves, armholes), and requires natural skin visible at the neck and upper arms, omitting the neck and shoulder base creates visible gaps whenever the camera angles change or short garments are equipped.

### B. Bone Weight Discrepancies
- **Cause:** 
  - `Andri_Head` is assigned to only a single vertex group (`spine.005`). There is no vertex weighting for `spine.004` (neck) or `spine.003` (upper chest/trapezius).
  - `Andri_Arms` has weights for `upper_arm.L/R`, but starts at X = +/-0.239m while the shoulder bones originate further inward at X = +/-0.018m to +/-0.195m. The inner deltoid and pectoral transition are completely missing.

### C. Stray Photogrammetry Mesh
- **Cause:** `Andri_Hair` or `Andri_Head` contains a cluster of background planar vertices (Z = 1.45m - 1.65m, Y > 0.05m) generated during initial image-to-3D projection or scan meshing that was not retopologized.

---

## 3. Step-by-Step Remediation Plan for 3D Artist / AI Model

To resolve these defects permanently, execute the following steps in Blender 4.x or 5.x:

### Step 1: Clean Up Scene and Eliminate Stray Armatures
1. Open `andri_fullbody_rigged.blend`.
2. Select and delete the 5 unlinked redundant armatures:
   - `metarig`
   - `metarig.001`
   - `metarig.002`
   - `metarig.003`
   - `metarig.004`
3. Retain only `Andri_Humanoid_Rig` (56 bones) as the master skeleton.

### Step 2: Remove the Flat Board Artifact Behind Head
1. Isolate `Andri_Head` and `Andri_Hair`.
2. Enter Edit Mode (`Tab`).
3. Switch to Face Selection mode. Inspect the region directly behind the skull (Y > 0.04m, Z between 1.45m and 1.65m).
4. Select all coplanar / flat background faces that do not form the skull curvature or hair strands.
5. Delete Faces (`X` -> `Faces`).
6. Verify in solid/rendered view that the silhouette of the occiput is clean and rounded.

### Step 3: Model and Bridge the Anatomical Neck
1. Enter Edit Mode on `Andri_Head`.
2. Select the open boundary edge loop at the base of the jaw/cranium (approx. Z = 1.540m).
3. Extrude downward (`E` -> `Z`) in 3 to 4 rings toward the collarbone level (Z = 1.460m):
   - Ring 1: Upper neck / submandibular transition.
   - Ring 2: Mid-neck / thyroid cartilage profile.
   - Ring 3: Lower neck flare / sternocleidomastoid transition.
   - Ring 4: Base of neck / upper trapezius and clavicle collar.
4. Scale and shape the rings to match human male anatomy (approx. diameter 11cm - 13cm).
5. Assign the new neck vertices to vertex group `spine.004` (Neck bone) with a smooth weight gradient transitioning to `spine.003` at the bottom and `spine.005` at the top.
6. Ensure material `M_Andri_Skin_Master` is assigned to these new polygons, with UV unwrapped into the existing neck/skin UV island.

### Step 4: Model Seamless Shoulders and Bridge Floating Arms
1. In Edit Mode on `Andri_Arms`:
   - Select the open boundary edge loop at the top of the upper arms (`upper_arm.L` and `upper_arm.R`, approx. Z = 1.44m, X = +/-0.24m).
   - Extrude inward along the shoulder axis toward the torso center (`E` -> inward toward X = +/-0.10m, Z = 1.48m - 1.52m).
   - Form the deltoid cap, acromion, and upper chest/pectoral base.
2. Weight painting for new shoulder geometry:
   - Weight vertices to `shoulder.L` / `shoulder.R` and `upper_arm.L` / `upper_arm.R`.
   - Blend weights into `spine.003` (Chest) so that when the armature rotates, the shoulder socket moves naturally without pinching.
3. Verify that the arm mesh extends comfortably inside the T-shirt sleeve opening by at least 6cm - 8cm, so that under any camera angle or animation pose, the interior of the shirt displays solid skin rather than empty air.

### Step 5: Fix Inverted UV Coordinates on T-Shirt Back
1. Select `Andri_TShirt`, enter Edit Mode, and open the UV Editor.
2. Select all faces whose normals face backwards (Y > 0).
3. In UV space (where back UVs reside, typically U in [0.5, 1.0]):
   - Mirror UV coordinates horizontally: `S` -> `X` -> `-1`.
   - Reposition and align them so that back decals and lettering read left-to-right correctly in `fitting-room.php`.

### Step 6: Verify and Export Clean glTF (.glb)
1. Ensure all character meshes have their Armature modifier pointing to `Andri_Humanoid_Rig`.
2. Ensure transforms are applied (`Ctrl + A` -> `All Transforms`).
3. Export to binary glTF:
   - File Path: `c:\laragon\www\tnm\marketplace\assets\models\andri_fullbody_rigged.glb`
   - Include: Selected Objects (or active collection containing character meshes + rig).
   - Data - Mesh: Apply Modifiers = True, UVs = True, Normals = True, Vertex Colors = False.
   - Animation: Export Deformation Bones Only.
4. Verify file size: Clean export should be between 7MB and 9MB.

---

## 4. Acceptance Checklist for the Fix

Before finalizing the task, verify the following in `http://localhost/tnm/fitting-room.php`:
- [ ] Rotating the character 360 degrees shows a continuous anatomical neck under the chin.
- [ ] No flat board or planar artifact visible behind the head from rear angles.
- [ ] T-shirt sleeves show solid arm/shoulder geometry inside; no floating arm gap.
- [ ] Switching between T-shirt and Hoodie maintains natural hand and neck positioning.
- [ ] Text decals applied to the back of the T-shirt read correctly left-to-right.
- [ ] Browser console reports zero WebGL or Three.js asset loading errors.
