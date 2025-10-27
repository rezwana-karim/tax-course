# User Testing Guide - Tax Course Platform

## Overview
This document provides comprehensive testing scenarios for the Tax Course Creation Platform with step-by-step instructions for manual testing.

## Testing Environment Setup

### Prerequisites
1. Laravel server running (`php artisan serve`)
2. Database migrated
3. Frontend assets built (`npm run build`)
4. Storage linked (`php artisan storage:link`)

## Test Scenarios

### Scenario 1: Guest User - Browse Courses
**Steps**:
1. Navigate to `http://localhost:8000/courses`
2. Verify "Login" and "Register" links appear
3. Click on a course to view details

**Expected**: Course browsing works for guests without edit options

### Scenario 2: Create Course with Files
**Steps**:
1. Login as instructor
2. Navigate to `/courses/create`
3. Fill form with title, description, category
4. Upload thumbnail and video
5. Add module with content (including file upload for video/document content)
6. Submit

**Expected**: Course created with all files properly stored

See full guide in this file for all 15 test scenarios.
