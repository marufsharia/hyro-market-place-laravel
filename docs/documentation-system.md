# Documentation System - Complete Guide

## Overview
A comprehensive documentation system has been added to the Hyro marketplace, providing users with searchable, categorized, and version-controlled documentation.

## Features Implemented

### 1. Documentation Categories
- **Icon-based navigation** - Each category has a custom emoji icon
- **Organized structure** - Categories group related documentation
- **Description** - Each category has a helpful description
- **Order control** - Categories can be ordered for optimal UX

### 2. Documentation Pages
- **Rich content** - Full HTML support with code blocks, lists, headings
- **Version control** - Track documentation across different versions
- **Search functionality** - Full-text search across all documentation
- **View tracking** - Track how many times each doc is viewed
- **Tags** - Tag-based organization for better discovery

### 3. User Interface

#### Index Page (`/docs`)
- **Category grid** - Visual cards showing all categories
- **Search bar** - Search across all documentation
- **Version filter** - Filter by specific versions
- **Category filter** - View docs from specific category
- **Pagination** - Handle large numbers of docs efficiently

#### Show Page (`/docs/{slug}`)
- **Three-column layout**:
  - Left sidebar: Category navigation with nested docs
  - Main content: Documentation with breadcrumbs
  - Right sidebar: Table of contents + related articles
- **Auto-generated TOC** - Extracted from H2 and H3 headings
- **Active heading tracking** - Highlights current section in TOC
- **Smooth scrolling** - Click TOC items to jump to sections
- **Previous/Next navigation** - Navigate between docs in same category
- **Related articles** - Shows other docs in same category
- **Breadcrumb navigation** - Easy navigation back to index
- **View counter** - Displays view count
- **Last updated date** - Shows when doc was last modified
- **Tags display** - Shows all tags for the document

### 4. Content Features
- **Syntax highlighting** - Code blocks with proper styling
- **Responsive design** - Works on mobile, tablet, desktop
- **Dark mode support** - Full dark mode compatibility
- **Prose styling** - Beautiful typography for readability
- **Inline links** - Styled links within content
- **Lists and tables** - Proper formatting for all HTML elements

## Database Schema

### `documentation_categories` Table
```sql
- id (bigint, primary key)
- name (varchar) - Category name
- slug (varchar, unique) - URL-friendly identifier
- description (text, nullable) - Category description
- icon (varchar, nullable) - Emoji or icon
- order (integer) - Display order
- timestamps
```

### `documentations` Table
```sql
- id (bigint, primary key)
- category_id (foreign key) - Links to category
- title (varchar) - Document title
- slug (varchar, unique) - URL-friendly identifier
- excerpt (text, nullable) - Short description
- content (longtext) - Full HTML content
- version (varchar) - Version number (e.g., "1.0")
- order (integer) - Order within category
- is_published (boolean) - Publish status
- tags (json, nullable) - Array of tags
- views (integer) - View count
- timestamps
```

### Indexes
- slug (both tables) - Fast lookups by slug
- category_id - Fast category filtering
- version - Fast version filtering
- is_published - Fast published docs queries
- order - Fast ordered queries

## Routes

```php
GET  /docs                    - Documentation index
GET  /docs/{slug}             - View specific documentation
```

## Controllers

### DocumentationController
- `index()` - List all docs with search/filter
- `show()` - Display single doc with related content

## Models

### Documentation
- Relationships: `belongsTo(DocumentationCategory)`
- Scopes: `published()`, `version($version)`
- Methods: `incrementViews()`, `generateUniqueSlug()`
- Auto-generates slug from title on creation

### DocumentationCategory
- Relationships: `hasMany(Documentation)`, `publishedDocumentations()`
- Ordered by `order` field

## Seeded Content

### Categories (5)
1. **Getting Started** 🚀 - Basics and quick start
2. **Plugin Development** 🔧 - Building plugins
3. **API Reference** 📡 - API documentation
4. **Best Practices** ⭐ - Guidelines and tips
5. **Troubleshooting** 🔍 - Common issues

### Documentation (12 articles)

#### Getting Started
- Introduction to Hyro
- Installation Guide
- Quick Start Tutorial

#### Plugin Development
- Creating Your First Plugin
- Plugin Structure
- Testing Plugins

#### API Reference
- Authentication API
- Plugin API

#### Best Practices
- Security Best Practices
- Performance Optimization

#### Troubleshooting
- Common Installation Issues
- Plugin Compatibility Issues

## Frontend Components

### Index.jsx
- Search form with version filter
- Category grid display
- Documentation list with pagination
- Filter management
- Responsive layout

### Show.jsx
- Three-column responsive layout
- Auto-generated table of contents
- Intersection Observer for active heading
- Smooth scroll navigation
- Related articles sidebar
- Category navigation sidebar
- Previous/Next navigation
- Breadcrumb trail

## Usage Examples

### Adding New Documentation

```php
Documentation::create([
    'category_id' => $category->id,
    'title' => 'My New Doc',
    'excerpt' => 'Short description',
    'content' => '<h2>Heading</h2><p>Content...</p>',
    'version' => '1.0',
    'order' => 1,
    'is_published' => true,
    'tags' => ['tag1', 'tag2'],
]);
```

### Searching Documentation

```php
$docs = Documentation::published()
    ->where('title', 'like', '%search%')
    ->orWhere('content', 'like', '%search%')
    ->get();
```

### Getting Docs by Category

```php
$docs = Documentation::published()
    ->whereHas('category', fn($q) => $q->where('slug', 'getting-started'))
    ->orderBy('order')
    ->get();
```

### Filtering by Version

```php
$docs = Documentation::published()
    ->version('1.0')
    ->get();
```

## Navigation Integration

The documentation link has been added to:
- Desktop navigation menu
- Mobile navigation menu
- Accessible from all pages via Navbar component

## Styling

### Prose Classes
- Custom typography for documentation content
- Code block styling with dark background
- Inline code with light background
- Link styling with hover effects
- Heading hierarchy with proper spacing
- List styling for better readability

### Responsive Design
- Mobile: Single column, stacked layout
- Tablet: Two columns
- Desktop: Three columns with sticky sidebars

### Dark Mode
- Full dark mode support
- Proper contrast ratios
- Smooth transitions between modes

## Performance Optimizations

1. **Indexes** - All frequently queried fields are indexed
2. **Eager Loading** - Categories loaded with docs to avoid N+1
3. **Pagination** - Large doc lists are paginated
4. **View Increment** - Efficient counter increment
5. **Caching Ready** - Structure supports caching layer

## SEO Features

- Semantic HTML structure
- Proper heading hierarchy
- Meta descriptions (excerpt field)
- Clean URLs with slugs
- Breadcrumb navigation
- Internal linking (related articles)

## Accessibility

- Keyboard navigation support
- ARIA labels where needed
- Proper heading structure
- Focus management
- Screen reader friendly
- High contrast in dark mode

## Future Enhancements (Optional)

1. **Search Improvements**
   - Full-text search with ranking
   - Search suggestions
   - Search history

2. **Content Features**
   - Markdown support
   - Code syntax highlighting with language detection
   - Embedded videos
   - Interactive examples
   - Downloadable code samples

3. **User Features**
   - Bookmark favorite docs
   - Print-friendly version
   - PDF export
   - Share on social media
   - Feedback/rating system

4. **Admin Features**
   - WYSIWYG editor
   - Draft/publish workflow
   - Version comparison
   - Analytics dashboard
   - Bulk operations

5. **Advanced Features**
   - Multi-language support
   - API documentation generator
   - Changelog integration
   - Community contributions
   - Comments/discussions

## Testing Recommendations

1. **Unit Tests**
   - Model methods
   - Slug generation
   - View increment
   - Scopes

2. **Feature Tests**
   - Index page loads
   - Show page loads
   - Search functionality
   - Filtering works
   - Pagination works

3. **Browser Tests**
   - Navigation works
   - TOC scrolling
   - Responsive layout
   - Dark mode toggle
   - Search form submission

## Maintenance

### Adding New Categories
1. Create category in database
2. Add icon emoji
3. Set appropriate order
4. Add description

### Adding New Documentation
1. Write content in HTML
2. Set appropriate category
3. Add tags for discoverability
4. Set version number
5. Order within category
6. Publish when ready

### Updating Documentation
1. Edit content
2. Update version if needed
3. Update timestamp (automatic)
4. Consider adding changelog entry

### Managing Versions
- Use semantic versioning (1.0, 1.1, 2.0)
- Keep old versions for reference
- Mark deprecated versions
- Provide migration guides

## Security Considerations

- HTML content is stored as-is (trusted admin input)
- Output is rendered with `dangerouslySetInnerHTML` (admin-controlled)
- Search input is sanitized
- No user-generated content in docs
- Published flag prevents showing drafts

## Conclusion

The documentation system provides a professional, user-friendly way to deliver comprehensive documentation to Hyro users. It's fully integrated with the existing design system, supports dark mode, and is optimized for both performance and user experience.
