# Security Policy

## Reporting Security Issues

We take the security of this project seriously. If you discover a vulnerability or security flaw, please report it responsibly rather than opening a public issue.

### Process
1. Contact the repository maintainers directly or use GitHub Private Vulnerability Reporting if enabled.
2. Provide a detailed summary with steps to reproduce and proof-of-concept where possible.
3. Allow reasonable time for the maintainers to investigate and issue a patch before public disclosure.

### Scope
- PHP backend code (`config.php`, `admin/`, `api/`)
- CSRF validation, rate limiting, and session security
- SQL injection prevention (all queries must use PDO prepared statements)
- Cross-Site Scripting (XSS) prevention on user input rendering
- Never commit live credentials, API keys, or proprietary production customer data.
