# Advanced Book Listing

A WordPress plugin for managing and displaying books with advanced filtering, AJAX pagination, and REST API support.

## Description

Advanced Book Listing is a feature-rich WordPress plugin that provides a complete solution for managing and showcasing books on your WordPress site. It includes custom post types, advanced filtering options, AJAX-powered pagination, and a REST API endpoint for dynamic data fetching.

## Features

- **Custom Post Type**: Dedicated "Books" post type with custom fields
    - Author Name
    - Price
    - Publish Date
    - Featured Image support

- **Advanced Filtering**:
    - Filter by Author Name (A-Z)
    - Filter by Price Range ($50-$200)
    - Sort by Publish Date (Newest/Oldest)

- **AJAX Functionality**:
    - Dynamic content loading
    - Load More pagination
    - Real-time filter updates

- **Performance Optimized**:
    - Transient caching
    - Optimized database queries
    - Minified assets

- **REST API Integration**:
    - Custom endpoint for book data
    - Filter support via query parameters
    - JSON response format

## Installation

1. Upload the `advanced-book-listing` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings under 'Books' in the admin menu

## Usage

### Shortcode

Use the following shortcode to display the book listing:

```
[advanced_books]
```


### REST API Endpoint

#### Get Books List

```
GET /wp-json/books/v1/list
```

Parameters:
- `author`: Filter by author name
- `price_range`: Filter by price range (e.g., "50-100")
- `sort_by`: Sort by date ("newest" or "oldest")
- `page`: Page number
- `per_page`: Items per page

Example Response:
```json
{
  "books": [
    {
      "id": 1,
      "title": "Book Title",
      "author": "Author Name",
      "price": 99.99,
      "publish_date": "2023-11-20",
      "thumbnail": "https://example.com/image.jpg",
      "excerpt": "Book excerpt...",
      "link": "https://example.com/book"
    }
  ],
  "total_pages": 5,
  "total_books": 45,
  "current_page": 1
}
```

## Development

### Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher

### Setup Development Environment

1. Clone the repository:
```bash
git clone https://github.com/mrmakadia94/advanced-book-listing.git
```

### File Structure

```
advanced-book-listing/
├── admin/
│   └── class-advanced-book-listing-admin.php
├── includes/
│   ├── class-advanced-book-listing.php
│   ├── class-advanced-book-activator.php
│   ├── class-advanced-book-deactivator.php
│   ├── class-advanced-book-listing-loader.php
│   ├── class-advanced-book-listing-i18n.php
│   └── class-advanced-book-listing-rest-api.php
├── public/
│   ├── css/
│   │   └── advanced-book-listing-public.css
│   └── js/
│       └── advanced-book-listing-public.js
├── package.json
├── advanced-book-listing.php
└── README.md
```


## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Credits

- [WordPress Plugin Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate)

## Changelog

### 1.0.0
- Initial release
- Basic book management functionality
- AJAX filtering and pagination
- REST API endpoint