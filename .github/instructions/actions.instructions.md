# GitHub Actions Workflow Instructions

applyTo:
  - ".github/workflows/**/*.yml"
  - ".github/workflows/**/*.yaml"

## Purpose

These instructions guide GitHub Copilot when working with GitHub Actions workflow files in this repository.

## Workflow Structure

### Existing Workflows

1. **tests.yml** - Runs PHPUnit tests on multiple PHP versions
2. **issues.yml** - Handles issue automation
3. **pull-requests.yml** - Handles PR automation
4. **update-changelog.yml** - Manages changelog updates

## Best Practices for GitHub Actions Workflows

### General Guidelines

- Use descriptive workflow names
- Specify appropriate triggers (`push`, `pull_request`, `schedule`, etc.)
- Use matrix strategy for testing across multiple versions/environments
- Set appropriate permissions (principle of least privilege)
- Use meaningful job and step names
- Add comments for complex logic

### PHP/Laravel Specific Workflows

#### Testing Workflow Pattern
```yaml
name: Tests

on:
  push:
    branches: [master, main, '*.x']
  pull_request:
  
permissions:
  contents: read

jobs:
  tests:
    runs-on: ubuntu-latest
    
    strategy:
      fail-fast: true
      matrix:
        php: [8.2, 8.3, 8.4]
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite
          coverage: none
      
      - name: Install dependencies
        run: composer install --prefer-dist --no-interaction --no-progress
      
      - name: Setup environment
        run: cp .env.example .env
      
      - name: Generate app key
        run: php artisan key:generate
      
      - name: Execute tests
        run: php artisan test
```

### Common Actions to Use

#### Checkout
```yaml
- name: Checkout code
  uses: actions/checkout@v4
```

#### PHP Setup
```yaml
- name: Setup PHP
  uses: shivammathur/setup-php@v2
  with:
    php-version: '8.3'
    extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite
    coverage: xdebug  # or 'none' if coverage not needed
```

#### Node.js Setup
```yaml
- name: Setup Node.js
  uses: actions/setup-node@v4
  with:
    node-version: '20'
    cache: 'npm'
```

#### Caching Dependencies
```yaml
- name: Cache Composer dependencies
  uses: actions/cache@v3
  with:
    path: vendor
    key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
    restore-keys: ${{ runner.os }}-composer-

- name: Cache npm dependencies
  uses: actions/cache@v3
  with:
    path: node_modules
    key: ${{ runner.os }}-node-${{ hashFiles('**/package-lock.json') }}
    restore-keys: ${{ runner.os }}-node-
```

### Testing Guidelines

#### Run Laravel Tests
```yaml
- name: Run tests
  run: php artisan test
  
# Or with coverage
- name: Run tests with coverage
  run: php artisan test --coverage
```

#### Code Quality Checks
```yaml
- name: Run Laravel Pint
  run: ./vendor/bin/pint --test

- name: Run static analysis
  run: ./vendor/bin/phpstan analyse
```

#### Database Testing
```yaml
- name: Run migrations
  run: php artisan migrate --force

- name: Seed database
  run: php artisan db:seed --force
```

### Deployment Workflows

#### Environment Variables
```yaml
- name: Set environment variables
  run: |
    echo "APP_ENV=production" >> .env
    echo "APP_DEBUG=false" >> .env
```

#### Build Assets
```yaml
- name: Install npm dependencies
  run: npm ci

- name: Build assets
  run: npm run build
```

### Security Best Practices

1. **Never expose secrets in logs**
   ```yaml
   - name: Deploy
     run: echo "Deploying..."
     env:
       API_KEY: ${{ secrets.API_KEY }}  # Good
   # Don't: echo ${{ secrets.API_KEY }}  # Bad
   ```

2. **Use GitHub Secrets for sensitive data**
   - Store in repository settings → Secrets and variables → Actions
   - Access via `${{ secrets.SECRET_NAME }}`

3. **Pin action versions**
   ```yaml
   # Good - specific version
   uses: actions/checkout@v4
   
   # Better - commit SHA for security-critical workflows
   uses: actions/checkout@8ade135a41bc03ea155e62e844d188df1ea18608
   ```

4. **Set minimal permissions**
   ```yaml
   permissions:
     contents: read
     pull-requests: write  # only if needed
   ```

### Performance Optimization

1. **Use caching for dependencies**
2. **Use `fail-fast: true` for matrix builds**
3. **Run jobs in parallel when possible**
4. **Use conditional execution**
   ```yaml
   - name: Deploy
     if: github.ref == 'refs/heads/main' && github.event_name == 'push'
     run: ./deploy.sh
   ```

### Error Handling

```yaml
- name: Run tests
  run: php artisan test
  continue-on-error: false  # fail workflow if tests fail

- name: Upload logs on failure
  if: failure()
  uses: actions/upload-artifact@v3
  with:
    name: laravel-logs
    path: storage/logs/
```

### Artifact Management

```yaml
- name: Upload coverage report
  uses: actions/upload-artifact@v3
  with:
    name: coverage-report
    path: coverage/
    retention-days: 30
```

### Workflow Triggers

```yaml
on:
  # On push to specific branches
  push:
    branches: [main, develop]
    paths-ignore:
      - '**.md'
      - 'docs/**'
  
  # On pull requests
  pull_request:
    types: [opened, synchronize, reopened]
  
  # Scheduled runs
  schedule:
    - cron: '0 0 * * *'  # Daily at midnight
  
  # Manual trigger
  workflow_dispatch:
    inputs:
      environment:
        description: 'Environment to deploy to'
        required: true
        default: 'staging'
```

### Matrix Strategy

```yaml
strategy:
  fail-fast: true
  matrix:
    php: [8.2, 8.3, 8.4]
    laravel: [11, 12]
    dependency-version: [prefer-lowest, prefer-stable]
    exclude:
      - php: 8.2
        laravel: 12
```

### Environment Variables

```yaml
env:
  GLOBAL_VAR: 'value'

jobs:
  test:
    env:
      JOB_VAR: 'value'
    steps:
      - name: Test
        env:
          STEP_VAR: 'value'
        run: echo "Testing"
```

## Laravel-Specific Considerations

### Database Setup
```yaml
- name: Setup SQLite
  run: |
    touch database/database.sqlite
    php artisan migrate --force
```

### Queue Workers
```yaml
- name: Test queue workers
  run: |
    php artisan queue:work --stop-when-empty &
    php artisan queue:work --stop-when-empty
```

### Asset Compilation
```yaml
- name: Compile assets
  run: |
    npm ci
    npm run build
```

## Common Patterns for This Project

### Full CI/CD Pipeline
```yaml
name: CI/CD

on:
  push:
    branches: [main]
  pull_request:

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
      - run: composer install
      - run: cp .env.example .env
      - run: php artisan key:generate
      - run: php artisan test
      - run: ./vendor/bin/pint --test
  
  deploy:
    needs: test
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Deploy
        run: echo "Deploy to production"
```

## Troubleshooting

### Common Issues

1. **Composer install fails**
   - Ensure PHP version matches requirements
   - Check composer.lock is committed
   - Try `composer install --no-scripts`

2. **Tests fail**
   - Verify environment is set up correctly
   - Check database migrations
   - Ensure .env.example is up to date

3. **Permission errors**
   - Set appropriate permissions in workflow
   - Use `sudo` when necessary

## Documentation

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel GitHub Actions](https://github.com/laravel/framework/blob/11.x/.github/workflows/tests.yml)
- [Actions Marketplace](https://github.com/marketplace?type=actions)

## Notes

- Always test workflows locally when possible using [act](https://github.com/nektos/act)
- Use workflow visualization to understand complex workflows
- Keep workflows DRY using reusable workflows and composite actions
- Monitor workflow execution time and optimize slow steps
- Review security advisories for actions you use
