# Contributing to 3D Apparel Studio and Marketplace

Thank you for your interest in contributing to the 3D Apparel Studio and Marketplace project. We welcome contributions from developers, 3D artists, designers, and testers.

---

## Code of Conduct

We are committed to providing a welcoming, inclusive, and harassment-free environment for everyone. Please be respectful, constructive, and collaborative in all discussions and pull requests.

---

## How to Contribute

### 1. Reporting Bugs
- Before filing a bug report, check existing issues to ensure the problem has not already been reported.
- Include a descriptive title and clear steps to reproduce the issue.
- State your environment: browser name, version, operating system, and PHP version.
- Include browser console logs and PHP error logs if applicable.

### 2. Suggesting Features
- Open an issue outlining the proposed feature and why it would be valuable.
- Provide mockup sketches, references, or API examples if available.

### 3. Submitting Pull Requests
1. Fork the repository and create your feature branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```
2. Commit your changes with clear, descriptive commit messages:
   ```bash
   git commit -m "Add crewneck sweatshirt 3D model asset and presets"
   ```
3. Push to your branch:
   ```bash
   git push origin feature/your-feature-name
   ```
4. Open a Pull Request against the `main` branch.
5. Describe the problem your PR solves and test cases conducted.

---

## Coding Standards

### PHP
- Follow standard PHP PSR coding conventions.
- Maintain compatibility with PHP 7.4 through PHP 8.3+.
- Use PDO prepared statements with parameter binding for all database operations.
- Always include CSRF token verification on all POST/mutation handlers.
- Escape user-supplied data using `htmlspecialchars()` or the `e()` helper when outputting to HTML.

### JavaScript & WebGL
- Write modern, vanilla JavaScript (ES6+). Avoid unnecessary heavy external frameworks.
- Clean up Three.js geometries, materials, and textures when switching models to prevent WebGL memory leaks.
- Ensure all 3D scene interactions are mobile-touch responsive (supporting pinch-to-zoom and touch rotation).

### CSS & Design System
- Utilize CSS Custom Properties (`var(--ink)`, `var(--surface)`, `var(--red)`) defined in `assets/css/marketplace.css`.
- Ensure dark and light mode compatibility.
- Ensure full responsiveness across screen sizes from 320px mobile viewports to 4K desktop screens.

---

## 3D Asset Contribution Rules

When contributing new `.glb` apparel models:
- Model must be centered at `(0, 0, 0)` in Blender.
- Apply all transforms (Location, Rotation, Scale) before export.
- Keep polygon counts optimized (maximum 60,000 vertices per garment).
- Include both front and back UV unwrap channels.
- Test that decal projection aligns accurately on the mesh surface in `customizer.php`.

---

## Security Vulnerability Reporting

If you discover a potential security vulnerability, please do not disclose it in a public issue. Contact the repository maintainers directly with details and reproduction steps so a fix can be prepared and released promptly.
