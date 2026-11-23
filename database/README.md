# Database Documentation & Schemas

The 3D Apparel Marketplace utilizes a zero-configuration SQLite architecture with automated schema generation and initial data seeding.

## Automatic Bootstrapping
When the application is loaded for the first time, `config.php` checks for the presence of `data/marketplace.db`. If the file does not exist, the database connection automatically executes table creation DDL and populates the catalog with default products, vector preset artworks, and a default administrator user (`admin` / `admin123`).

## Manual Setup / Migration
If you prefer to initialize the database manually:

```bash
# Using SQLite CLI
sqlite3 data/marketplace.db < database/schema.sql
sqlite3 data/marketplace.db < database/seeds.sql
```

## Schema Files
- `schema.sql`: Contains DDL table definitions (`admin_users`, `products`, `preset_designs`, `orders`, `order_items`, `apparel_drafts`) and query performance indexes.
- `seeds.sql`: Contains default merchandise catalog items, 3D customizer vector presets, and initial admin credentials.
