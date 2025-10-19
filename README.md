# News Aggregator Backend

A Laravel-based news aggregation system that fetches articles from multiple external sources and provides RESTful API endpoints for frontend consumption.

## Project Overview

This application implements a comprehensive news aggregation backend that:

- **Fetches articles** from multiple news sources (NewsData.io, The Guardian, NewsAPI)
- **Aggregates content** using scheduled jobs and background processing
- **Provides RESTful APIs** for article listing, searching, and filtering
- **Manages sources dynamically** through database-driven configuration
- **Follows clean architecture** with Repository pattern, Service layer, and Laravel Resources

### Key Features

- **Multi-source news aggregation**: Supports NewsData.io, The Guardian, and NewsAPI with rate limiting
- **Smart fetching**: Incremental updates based on last fetch time
- **Dynamic data extraction**: Auto-extracts categories and authors from API responses
- **Advanced filtering**: By source, category, author with comprehensive search
- **Duplicate prevention**: External ID tracking per source
- **API documentation**: Auto-generated with Scribe
- **Background processing**: Laravel jobs for data fetching

### Architecture

- **Repository Pattern**: All database queries abstracted in repositories
- **Service Layer**: Business logic and external API integrations  
- **Laravel Resources**: Standardized API response formatting with pagination
- **Enum-driven Configuration**: Database-seeded source management
- **Job-based Processing**: Scheduled news fetching via Laravel queues

## Requirements

- PHP 8.2+
- Composer
- Node.js & NPM
- SQLite (default) or MySQL/PostgreSQL

## Setup

Follow these steps to set up the project:

1. **Clone and install dependencies:**
   ```bash
   git clone <repository-url>
   cd news_bank
   composer install
   npm install
   ```

2. **Environment configuration:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database setup:**
   ```bash
   touch database/database.sqlite  # For SQLite
   php artisan migrate
   php artisan db:seed --class=NewsSourceSeeder
   ```

The seeder will automatically configure all news sources with API keys.

## Running Tests

### Full Test Suite

```bash
composer run test
```

### Manual Test Commands

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run tests with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/ArticleTest.php

# Run with verbose output
php artisan test --verbose
```

### Test Categories

- **Unit Tests**: Model relationships, service classes, repositories
- **Feature Tests**: API endpoints, authentication, data flow
- **Integration Tests**: External API interactions, job processing

## API Documentation

### Viewing API Documentation

The API documentation is automatically generated using Scribe:

1. **Generate documentation:**
   ```bash
   php artisan scribe:generate
   ```

2. **View documentation:**
   - Open `public/docs/index.html` in your browser
   - Or visit `http://localhost:8000/docs` when server is running

3. **Update documentation:**
   ```bash
   # After making API changes
   php artisan scribe:generate --force
   ```

### API Endpoints

All API endpoints are versioned under `/api/v1/`:

#### Articles
- `GET /api/v1/articles` - List articles with pagination, search, and filters

#### Resource Lists  
- `GET /api/v1/sources` - Get available news sources
- `GET /api/v1/categories` - Get available categories
- `GET /api/v1/authors` - Get available authors

#### Query Parameters for Articles
- `search` - Search term for title, description, or content
- `category` - Filter by category ID
- `author` - Filter by author ID  
- `source` - Filter by news source ID
- `column` - Sort by field (id, title, published_at)
- `dir` - Sort direction (asc, desc)
- `length` - Items per page (default: 10)

### Example API Calls

```bash
# Get latest articles (default 10 per page)
curl "http://localhost:8000/api/v1/articles"

# Search for specific term
curl "http://localhost:8000/api/v1/articles?search=technology"

# Filter by category ID and sort by published date
curl "http://localhost:8000/api/v1/articles?category=1&column=published_at&dir=desc"

# Get available sources
curl "http://localhost:8000/api/v1/sources"

# Combine search with filters and pagination
curl "http://localhost:8000/api/v1/articles?search=laravel&category=1&length=20"
```

## Data Sources

Current integrated sources with API keys (pre-configured in seeder):
- **NewsData.io**: Latest news from multiple categories
- **The Guardian**: Quality journalism and in-depth articles  
- **NewsAPI**: General news aggregation service

All sources are enabled by default and include:
- Rate limiting configuration
- Smart incremental fetching
- Automatic category/author extraction
- Duplicate article prevention

### Adding New Sources

1. Add source enum to `app/Enums/NewsSourceEnum.php`
2. Create service class in `app/Services/NewsAPI/`
3. Update `NewsSourceSeeder.php` with API configuration
4. Update `NewsAggregationService.php` service mapping
5. Implement data transformation logic

## Background Jobs

### Available Artisan Commands

```bash
# Fetch news from all enabled sources with detailed output
php artisan news:fetch

# Fetch from specific source (option available but not implemented yet)
php artisan news:fetch --source=newsdata_io

# Run via Laravel scheduler (add to crontab)
php artisan schedule:work
```

The `news:fetch` command provides detailed output including:
- Articles fetched vs. saved per source
- Date range information
- Success/failure status with emojis
- Total articles processed

## Database Schema

### Core Tables

- **news_sources**: External API source configurations (enum, name, api_key, status, rate_limit)
- **articles**: Aggregated news articles with full metadata (title, description, content, url, published_at)
- **categories**: Dynamic categories extracted from API responses
- **authors**: Author information extracted from articles

### Key Features

- **Unique constraints**: `external_id` + `source_id` prevents duplicates
- **Optimized indexes**: On published_at, category_id, author_id for fast filtering
- **Foreign key cascades**: Proper cleanup when sources/categories are deleted
- **Nullable relationships**: Articles can exist without category/author
- **URL uniqueness**: Prevents duplicate URLs across all sources

