# Project Brief: 3D Apparel Virtual Fitting Room & Marketplace

*Languages: [English](BRIEF.md) | [Bahasa Indonesia](BRIEF_ID.md)*

This document defines the background, objectives, technical requirements, and boundaries of scope for 3D artists, riggers, and WebGL developers contributing to the project.

---

## 1. Project Overview & Background

### Context
This repository contains a standalone, web-based 3D apparel e-commerce platform and interactive customizer built with Three.js (WebGL), PHP, and SQLite. Its flagship feature is the **Virtual Fitting Room** (`fitting-room.php`), which renders a rigged humanoid 3D avatar (Andri) trying on modular clothing items such as T-Shirts, Hoodies, Pants/Jeans, and modular accessories.

### Current Problem
The current 3D garment assets appear rigid and behave like static objects glued to the character's surface. They lack realistic fabric volume, natural cloth folds and wrinkles, and organic deformation when the character assumes poses or rotates in the viewport.

### Core Objective
Reconstruct and refine the 3D clothing system (Garment Mesh Geometry, Cloth Physics/Simulation, Body Conforming, and Rigging/Weight Painting) so apparel convincingly wraps the human body with natural folds and accurate fabric behavior when rendered in the browser via Three.js.

---

## 2. Technical & Quality Requirements

### A. 3D Modeling & Garment Construction
1. **Garment Geometry & Physical Volume:**
   - Clothes must be built as distinct physical garments with realistic thickness (approx. 1.5mm - 2.5mm), not paper-thin flat planes or skins directly cloned from body vertices.
   - Include authentic garment tailoring details: neckline/collar ribs, sleeve cuffs, hem folds, and realistic seam lines.
2. **Body Conforming & Anatomical Fit:**
   - Meshes must follow the character's body contours (shoulders, chest, armpits, back, waist, crotch, and thighs).
   - Maintain a natural physical clearance (offset) between the inner garment surface and the character's skin to eliminate mesh penetration and clipping.
3. **Fabric Physics & Draping Characteristics:**
   - Differentiate fabric weight and drape properties:
     - **T-Shirt (Cotton):** Soft, gravity-influenced drape with subtle tension folds around the chest and armpits.
     - **Hoodie (Fleece / Heavy French Terry):** Chunky silhouette, pronounced structural folds on sleeves, a volumetric kangaroo pocket, and an open, hollow hood structure.
     - **Pants / Jeans (Heavy Denim):** Rigid structure with characteristic horizontal whiskering, creasing behind knees, and natural ankle stacking.
4. **Rigging & Weight Painting:**
   - Every clothing item must have clean vertex weights transferred or painted to deform smoothly with the character's armature (`andri_fullbody_rigged`).
   - Vertices must not stretch abnormally, spike, or collapse during viewport rotation and animation.

### B. WebGL & Browser Optimization
1. **Target Format:** Binary glTF (`.glb`) exported alongside documented master Blender project files (`.blend`).
2. **Polygon Budget:**
   - Body Mesh: ~20,000 - 30,000 triangles.
   - Apparel Item (T-Shirt, Hoodie, or Pants): ~10,000 - 30,000 triangles per item.
   - Total scene count must remain under 100,000 triangles to guarantee a steady 60 FPS on modern mobile devices and standard consumer laptops.
3. **PBR Materials & Textures:**
   - Standard PBR Metallic-Roughness workflow.
   - Texture maps (BaseColor, Normal, Roughness) sized between 1024x1024 and 2048x2048 in compressed PNG or WebP formats.
4. **Decal / Stamping Compatibility:**
   - Front and back chest/torso UVs must be laid out cleanly on UV channel 0 without overlapping or distortion, enabling accurate real-time vector decal projection in `customizer.php` and `fitting-room.php`.

---

## 3. Project Scope

### In-Scope (Primary Focus)
1. **3D Apparel Asset Refinement & Remodeling:**
   - Refactor or rebuild clothing assets:
     - `assets/models/tshirt.glb`
     - `assets/models/hoodie.glb`
     - Pants/Jeans on `assets/models/andri_fullbody_rigged.glb`
   - Implement realistic fabric wrinkles and tension lines (via baked cloth simulation, high-to-low normal baking, or shape keys).
   - Weight paint and bind clothing meshes to the character skeleton in `assets/models/andri_fullbody_rigged.blend`.
2. **Three.js Viewport Integration:**
   - Ensure exported `.glb` files load seamlessly into `fitting-room.php` without clipping, missing materials, or shader compilation warnings.
   - Tune Three.js material properties (roughness, metalness, clearcoat) for natural fabric appearance under existing studio lighting presets.
3. **Workflow Documentation:**
   - Document any Blender modifiers or export settings used so future clothing drops can replicate the pipeline.

### Out-of-Scope (Do Not Touch)
1. **Main Portal Backend:**
   - All external website logic, articles, and databases outside the `marketplace/` directory are strictly out of scope.
2. **Frontend Architecture Overhaul:**
   - The platform relies on lightweight native Three.js ES Modules, HTML5, and Vanilla JS/CSS. Do not convert the project into Next.js, React, Vue, or heavy node-based frameworks.
3. **Server-Side Physics Engines:**
   - No dedicated server-side simulation backends. All physics, lighting, and viewport rendering must run client-side on user GPUs via WebGL.
4. **Direct Bank Payment Gateways:**
   - The checkout workflow is designed around cryptographic token verification and WhatsApp direct order dispatch. Integration with third-party payment gateways (e.g., Midtrans, Stripe) is not required for this phase.
5. **Webcam AR / Face Tracking:**
   - The fitting room focuses strictly on an interactive 3D orbital viewport with presets, not real-time webcam motion tracking.

---

## 4. Deliverables & Acceptance Criteria

A contribution is considered complete and eligible for merge when:
1. Updated `.glb` apparel files and the updated master `.blend` file are committed and functional in `fitting-room.php`.
2. Garments exhibit noticeable depth, natural cloth wrinkles, and zero skin clipping when inspected from 360-degree orbital angles.
3. Browser viewport maintains 60 FPS performance on standard hardware.
4. Pull request contains zero proprietary credentials, customer information, or absolute machine-specific file paths.
