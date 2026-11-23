import bpy
import bmesh
import math
import os

def clear_scene():
    bpy.ops.wm.read_factory_settings(use_empty=True)

def setup_apparel_uvs(obj):
    """
    Project UVs so front faces (Y < 0 or Z oriented) map to U: [0.0, 0.5],
    and back faces map to U: [0.5, 1.0].
    """
    mesh = obj.data
    bm = bmesh.new()
    bm.from_mesh(mesh)
    
    # Ensure UV layer exists
    uv_layer = bm.loops.layers.uv.verify()
    
    # Calculate bounding box in local space
    min_x = min(v.co.x for v in bm.verts)
    max_x = max(v.co.x for v in bm.verts)
    min_z = min(v.co.z for v in bm.verts)
    max_z = max(v.co.z for v in bm.verts)
    
    width = max(max_x - min_x, 0.001)
    height = max(max_z - min_z, 0.001)

    for face in bm.faces:
        is_front = face.normal.y < 0  # facing front (-Y in standard front view)
        for loop in face.loops:
            v = loop.vert
            # Normalized local coordinates [0, 1]
            nx = (v.co.x - min_x) / width
            nz = (v.co.z - min_z) / height
            
            if is_front:
                # Map to left half of texture: U in [0.03, 0.47]
                u = 0.04 + (nx * 0.42)
            else:
                # Map to right half of texture: U in [0.53, 0.97]
                u = 0.54 + ((1.0 - nx) * 0.42)
            v_coord = 0.05 + (nz * 0.90)
            loop[uv_layer].uv = (u, v_coord)
            
    bm.to_mesh(mesh)
    bm.free()

def create_tshirt():
    clear_scene()
    
    # Create base torso
    # Bounding dimensions roughly: Width 0.55m, Height 0.72m, Depth 0.22m
    bm = bmesh.new()
    
    # 1. Main Torso body
    # Generate layered rings from bottom hem (Z=0.0) to neck (Z=0.65)
    rings = 14
    radial = 28
    verts_grid = []
    
    for r in range(rings):
        t = r / (rings - 1)
        z = t * 0.68  # 0 to 0.68
        
        # Radii variation for anatomical fit and waist
        if t < 0.2:
            # Bottom hem: slightly wider flare
            rx = 0.27 + (0.2 - t) * 0.03
            ry = 0.125
        elif t < 0.6:
            # Waist/torso
            rx = 0.26
            ry = 0.12
        else:
            # Chest & shoulders
            rx = 0.28 + (t - 0.6) * 0.18
            ry = 0.13 + (t - 0.6) * 0.03
            
        ring_verts = []
        for i in range(radial):
            angle = (i / radial) * 2 * math.pi
            # Natural elliptical shape with subtle wrinkles
            wrinkle = math.sin(angle * 4 + t * 6) * 0.004 * (1.0 - abs(t - 0.5) * 1.5)
            x = math.cos(angle) * (rx + wrinkle)
            y = math.sin(angle) * (ry + wrinkle * 0.5)
            
            # Gentle shoulder slope at top
            if t > 0.8:
                shoulder_drop = abs(x) * 0.12
                z_actual = z - shoulder_drop
            else:
                z_actual = z
                
            v = bm.verts.new((x, y, z_actual))
            ring_verts.append(v)
        verts_grid.append(ring_verts)
        
    # Connect torso faces
    for r in range(rings - 1):
        for i in range(radial):
            next_i = (i + 1) % radial
            v1 = verts_grid[r][i]
            v2 = verts_grid[r][next_i]
            v3 = verts_grid[r + 1][next_i]
            v4 = verts_grid[r + 1][i]
            bm.faces.new((v1, v2, v3, v4))
            
    # 2. Add Left & Right Sleeves
    # Left Sleeve
    sleeve_radial = 14
    sleeve_rings = 7
    for is_right in [False, True]:
        sign = 1 if not is_right else -1
        s_verts_grid = []
        for sr in range(sleeve_rings):
            st = sr / (sleeve_rings - 1)
            # Center of sleeve travels out and slightly down/forward
            cx = sign * (0.32 + st * 0.16)
            cy = -0.01 + st * 0.02
            cz = 0.60 - st * 0.14
            s_radius = 0.095 - st * 0.012
            
            s_ring = []
            for i in range(sleeve_radial):
                angle = (i / sleeve_radial) * 2 * math.pi
                sx = cx + math.cos(angle) * 0.02 * sign
                sy = cy + math.cos(angle) * s_radius
                sz = cz + math.sin(angle) * s_radius
                v = bm.verts.new((sx, sy, sz))
                s_ring.append(v)
            s_verts_grid.append(s_ring)
            
        for sr in range(sleeve_rings - 1):
            for i in range(sleeve_radial):
                next_i = (i + 1) % sleeve_radial
                v1 = s_verts_grid[sr][i]
                v2 = s_verts_grid[sr][next_i]
                v3 = s_verts_grid[sr + 1][next_i]
                v4 = s_verts_grid[sr + 1][i]
                bm.faces.new((v1, v2, v3, v4))
                
    # 3. Add Crewneck Ribbed Collar
    collar_pts = 24
    c_ring1 = []
    c_ring2 = []
    for i in range(collar_pts):
        angle = (i / collar_pts) * 2 * math.pi
        x1 = math.cos(angle) * 0.115
        y1 = math.sin(angle) * 0.085
        z1 = 0.655
        
        x2 = math.cos(angle) * 0.135
        y2 = math.sin(angle) * 0.105
        z2 = 0.640
        c_ring1.append(bm.verts.new((x1, y1, z1)))
        c_ring2.append(bm.verts.new((x2, y2, z2)))
        
    for i in range(collar_pts):
        next_i = (i + 1) % collar_pts
        bm.faces.new((c_ring1[i], c_ring1[next_i], c_ring2[next_i], c_ring2[i]))

    # Create Object
    mesh = bpy.data.meshes.new("TShirtMesh")
    bm.to_mesh(mesh)
    bm.free()
    
    obj = bpy.data.objects.new("TShirt", mesh)
    bpy.context.collection.objects.link(obj)
    bpy.context.view_layer.objects.active = obj
    
    # Smooth shading
    for poly in mesh.polygons:
        poly.use_smooth = True
        
    # Add Subdivision Surface Modifier for smooth organic cloth folds
    sub = obj.modifiers.new("Subdiv", 'SUBSURF')
    sub.levels = 1
    sub.render_levels = 2
    
    # Add Solidify for fabric thickness
    sol = obj.modifiers.new("Solidify", 'SOLIDIFY')
    sol.thickness = 0.003
    
    # Apply modifiers
    bpy.ops.object.modifier_apply(modifier="Subdiv")
    bpy.ops.object.modifier_apply(modifier="Solidify")
    
    # Center origin to center of mass
    bpy.ops.object.origin_set(type='ORIGIN_CENTER_OF_MASS')
    obj.location = (0, 0, 0)
    
    # Setup UVs
    setup_apparel_uvs(obj)
    
    # Create Default Material
    mat = bpy.data.materials.new(name="ApparelMaterial")
    mat.use_nodes = True
    bsdf = mat.node_tree.nodes.get("Principled BSDF")
    if bsdf:
        bsdf.inputs['Roughness'].default_value = 0.85  # Cotton fabric matte
        if 'Specular IOR Level' in bsdf.inputs:
            bsdf.inputs['Specular IOR Level'].default_value = 0.2
        elif 'Specular' in bsdf.inputs:
            bsdf.inputs['Specular'].default_value = 0.2
    obj.data.materials.append(mat)
    
    return obj

def create_hoodie():
    clear_scene()
    
    bm = bmesh.new()
    
    # 1. Main Hoodie Body (Relaxed boxier streetwear cut)
    rings = 14
    radial = 28
    verts_grid = []
    
    for r in range(rings):
        t = r / (rings - 1)
        z = t * 0.72
        
        # Boxy fit, thicker profile
        rx = 0.30 + (t * 0.06)
        ry = 0.15 + (t * 0.02)
        
        ring_verts = []
        for i in range(radial):
            angle = (i / radial) * 2 * math.pi
            wrinkle = math.sin(angle * 3 + t * 4) * 0.006
            x = math.cos(angle) * (rx + wrinkle)
            y = math.sin(angle) * (ry + wrinkle)
            
            if t > 0.82:
                shoulder_drop = abs(x) * 0.10
                z_actual = z - shoulder_drop
            else:
                z_actual = z
                
            v = bm.verts.new((x, y, z_actual))
            ring_verts.append(v)
        verts_grid.append(ring_verts)
        
    for r in range(rings - 1):
        for i in range(radial):
            next_i = (i + 1) % radial
            v1 = verts_grid[r][i]
            v2 = verts_grid[r][next_i]
            v3 = verts_grid[r + 1][next_i]
            v4 = verts_grid[r + 1][i]
            bm.faces.new((v1, v2, v3, v4))
            
    # 2. Kangaroo Pocket (Depan Perut)
    p_w = 0.18
    p_h = 0.14
    p_z_base = 0.12
    p_depth = 0.022
    
    # 4 corner vertices extruded outwards
    v_p1 = bm.verts.new((-p_w * 0.7, -0.165 - p_depth, p_z_base + p_h))
    v_p2 = bm.verts.new((p_w * 0.7, -0.165 - p_depth, p_z_base + p_h))
    v_p3 = bm.verts.new((p_w, -0.165 - p_depth, p_z_base))
    v_p4 = bm.verts.new((-p_w, -0.165 - p_depth, p_z_base))
    bm.faces.new((v_p1, v_p2, v_p3, v_p4))
    
    # 3. Long Sleeves with ribbed cuffs
    sleeve_radial = 14
    sleeve_rings = 9
    for is_right in [False, True]:
        sign = 1 if not is_right else -1
        s_verts_grid = []
        for sr in range(sleeve_rings):
            st = sr / (sleeve_rings - 1)
            cx = sign * (0.34 + st * 0.26)
            cy = -0.01 + st * 0.03
            cz = 0.65 - st * 0.38
            s_radius = 0.11 - st * 0.032
            
            s_ring = []
            for i in range(sleeve_radial):
                angle = (i / sleeve_radial) * 2 * math.pi
                sx = cx + math.cos(angle) * 0.02 * sign
                sy = cy + math.cos(angle) * s_radius
                sz = cz + math.sin(angle) * s_radius
                v = bm.verts.new((sx, sy, sz))
                s_ring.append(v)
            s_verts_grid.append(s_ring)
            
        for sr in range(sleeve_rings - 1):
            for i in range(sleeve_radial):
                next_i = (i + 1) % sleeve_radial
                v1 = s_verts_grid[sr][i]
                v2 = s_verts_grid[sr][next_i]
                v3 = s_verts_grid[sr + 1][next_i]
                v4 = s_verts_grid[sr + 1][i]
                bm.faces.new((v1, v2, v3, v4))
                
    # 4. 3D Hood Structure
    hood_rings = 8
    hood_radial = 16
    h_verts_grid = []
    for hr in range(hood_rings):
        ht = hr / (hood_rings - 1)
        hz = 0.68 + ht * 0.28
        h_rx = 0.17 * math.sin((1.0 - ht * 0.5) * math.pi * 0.6)
        h_ry = 0.18
        h_cy = 0.06 + (ht * 0.04)
        
        h_ring = []
        for i in range(hood_radial):
            angle = (i / hood_radial) * 2 * math.pi
            hx = math.cos(angle) * h_rx
            hy = h_cy + math.sin(angle) * h_ry
            # Open front of hood
            if angle > math.pi * 0.75 and angle < math.pi * 1.25:
                hy += 0.05
            v = bm.verts.new((hx, hy, hz))
            h_ring.append(v)
        h_verts_grid.append(h_ring)
        
    for hr in range(hood_rings - 1):
        for i in range(hood_radial):
            next_i = (i + 1) % hood_radial
            v1 = h_verts_grid[hr][i]
            v2 = h_verts_grid[hr][next_i]
            v3 = h_verts_grid[hr + 1][next_i]
            v4 = h_verts_grid[hr + 1][i]
            bm.faces.new((v1, v2, v3, v4))

    mesh = bpy.data.meshes.new("HoodieMesh")
    bm.to_mesh(mesh)
    bm.free()
    
    obj = bpy.data.objects.new("Hoodie", mesh)
    bpy.context.collection.objects.link(obj)
    bpy.context.view_layer.objects.active = obj
    
    for poly in mesh.polygons:
        poly.use_smooth = True
        
    sub = obj.modifiers.new("Subdiv", 'SUBSURF')
    sub.levels = 1
    sub.render_levels = 2
    
    sol = obj.modifiers.new("Solidify", 'SOLIDIFY')
    sol.thickness = 0.005
    
    bpy.ops.object.modifier_apply(modifier="Subdiv")
    bpy.ops.object.modifier_apply(modifier="Solidify")
    
    bpy.ops.object.origin_set(type='ORIGIN_CENTER_OF_MASS')
    obj.location = (0, 0, 0)
    
    setup_apparel_uvs(obj)
    
    mat = bpy.data.materials.new(name="ApparelMaterial")
    mat.use_nodes = True
    bsdf = mat.node_tree.nodes.get("Principled BSDF")
    if bsdf:
        bsdf.inputs['Roughness'].default_value = 0.90  # Heavy Fleece matte
        if 'Specular IOR Level' in bsdf.inputs:
            bsdf.inputs['Specular IOR Level'].default_value = 0.15
        elif 'Specular' in bsdf.inputs:
            bsdf.inputs['Specular'].default_value = 0.15
    obj.data.materials.append(mat)
    
    return obj

def main():
    output_dir = r"C:\laragon\www\tnm\marketplace\assets\models"
    os.makedirs(output_dir, exist_ok=True)
    
    # 1. Build and export T-Shirt
    tshirt = create_tshirt()
    tshirt_path = os.path.join(output_dir, "tshirt.glb")
    bpy.ops.export_scene.gltf(
        filepath=tshirt_path,
        export_format='GLB',
        use_selection=False,
        export_apply=True
    )
    print(f"Exported T-Shirt to: {tshirt_path}")
    
    # 2. Build and export Hoodie
    hoodie = create_hoodie()
    hoodie_path = os.path.join(output_dir, "hoodie.glb")
    bpy.ops.export_scene.gltf(
        filepath=hoodie_path,
        export_format='GLB',
        use_selection=False,
        export_apply=True
    )
    print(f"Exported Hoodie to: {hoodie_path}")

if __name__ == "__main__":
    main()
