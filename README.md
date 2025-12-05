# Education Platform (Symfony)

Small learning platform built with Symfony and Doctrine.

It supports three roles:

- Admin
- Teacher
- Student

## Main features

### Public / students

- List all courses
- View course details (category, price, chapters, attachments, quizzes)
- Register and log in
- Enroll in a course
- See the list of enrolled courses (“My courses”)
- For each enrolled course:
  - Access the chapters
  - Open attachments (named links)
  - Open external quizzes (links to tools like Google Forms, etc.)

### Admin

- Manage categories (create, edit, delete)
- Manage courses (title, description, price, category)
- Manage chapters for a course
- Manage attachments for a course (name + URL)
- Manage quizzes for a course (title + external URL)
- Manage users by changing their roles directly in the database (for example, make a user an admin or teacher)

### Teacher

- Log in with a teacher account
- See only the courses they teach
- Create and edit their own courses
- For their own courses, manage:
  - Attachments (name + URL)
  - Quizzes (title + external URL)
- See the list of students enrolled in their courses (name, email, enrolled date)

---

## Requirements

- PHP 8.1 or higher
- Composer
- MySQL (or MariaDB)
- Symfony CLI is optional but convenient

---

## Installation

1. Install PHP and Composer.

2. Install project dependencies:

   ```bash
   composer install
