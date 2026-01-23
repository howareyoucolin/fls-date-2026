# Simple PHP Site Structure

This is a clean, simple PHP structure without MVC complexity. Perfect for SEO and easy to work with.

## Structure

```
public/
├── index.php          # Simple router
├── .htaccess          # Clean URLs
├── config.php         # Configuration (create from config.php.sample)
├── pages/             # Page files
│   ├── home.php       # Home page
│   └── 404.php        # 404 page
├── templates/         # Reusable templates
│   ├── header.php     # Header with navigation
│   └── footer.php     # Footer
└── includes/          # Shared code
    ├── db.php         # Database class
    └── functions.php  # Helper functions
```

## How It Works

1. **Clean URLs**: `.htaccess` routes all requests to `index.php`
2. **Simple Routing**: `index.php` maps URLs to page files in `pages/`
3. **Direct Pages**: Each page file in `pages/` is self-contained and easy to edit
4. **Templates**: Header and footer are included in each page

## Adding a New Page

1. Create a file in `pages/` (e.g., `pages/about.php`)
2. Add route mapping in `index.php` if needed
3. Include header/footer templates

Example:
```php
<?php
$meta_title = 'About Us';
include ROOT_PATH . '/templates/header.php';
?>
<div class="container">
    <h1>About Us</h1>
    <p>Content here...</p>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
```

## Database

Uses the `cz_members` table. Helper functions in `includes/functions.php`:
- `get_members($limit, $offset)` - Get members list
- `get_member($id)` - Get single member
- `get_profile_image_url($member)` - Get profile image
- `get_member_url($id)` - Get member page URL
- `calculate_age($birthday)` - Calculate age from birthday
- `truncate_text($text, $length)` - Truncate text

## SEO Features

- Clean URLs (no .php extensions)
- Proper meta tags in header
- Semantic HTML
- Mobile responsive
