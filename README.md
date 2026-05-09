<img src="public/images/Logo.png" alt="Bookworms" width="250" height="250"/>

# Bookworms
<p>"Between the books"</p>
<p>What is for reading this week</p>

## Requirements

Install the following before starting:

- Visual Studio Code: https://code.visualstudio.com/
- Laravel Herd: https://herd.laravel.com/windows
- PHP: https://windows.php.net/download/
- Node.js: https://nodejs.org/
- Composer: https://getcomposer.org/

---

## Clone the Repository

```bash
git clone https://github.com/Hamza2702/whatisforreadingthisweek.git
cd whatisforreadingthisweek
```

## Laravel Herd Setup
Add the project folder to Laravel Herd -
- Open Herd
- Go to Sites
- Add the project directory

## Environment Setup
Create the .env file:
```bash
cp .env.example .env
```

## Install Dependencies
### Install PHP dependencies
```bash
composer install
```
### Install Node dependencies
```bash
npm install
```
### Database Setup
Create and allow for a database to be set up.
Run migrations and seed the database
```bash
php artisan migrate --seed
```

#### Generate App Key
```bash
php artisan key:generate
```
### Storage Setup
```bash
php artisan storage:link
```
## Run the project
```bash
npm run dev
```
If it is needed, start the Laravel backend on another terminal:
```bash
php artisan serve
```
---
# Features
## Personalised Reading Recommendation Algorithm
- Multi-factor recommendation algorithm using Oxford Reading Tree (ORT) colour bands, reading history, peer-difficulty classification and pupil genre preferences
- Automated weekly reading list generation per pupil and entire class (full class of 30 generated in less than 3.66 seconds)
- Excludes previously read books from future recommendations

## Teacher Dashboard and Classroom Management
- Analytics dashboard with class reading progress and pupil statistics
- Create, archive and progress classrooms across academic years (archived classrooms preserved)
- Manage pupil profiles (including ORT reading level, genre preferences and weekly reading goals)
- Import pupils via CSV
- Send announcements to the class or individual pupils
- View and manage reading lists before assignment.

## Pupil Interface
- Dashboards showing current assignments, reading streaks and weekly goal progress
- Submit star ratings with difficulty levels and review descriptions on completed books
- View assignment history and personal reading progress statistics
- Unique kitten-themed usernames and child-friendly profile pictures! Cutee :3

## Book Catalogue
- Searchable catalogue of 30,000+ books (via Google Books API and Internet Archive)
- Filter by genre, ORT level, phonics sound, author, title and availability
- Stock availability tracking per school
- Teachers can add custom books and update stock levels
- School administrators can restrict/ban inappropriate books school-wide

## Engagement Features
- Reading streak tracking for consistent daily reading
- Teachers can configure weekly reading goals per pupil
- Monthly and all-time classroom leaderboards

## Role-based Access Controls
- Three roles: Pupil, Teacher, School Administrator
- Middleware-enforced routing protection per role
- Multi-tenant architecture (each school's data is fully isolated)

## School Administration
- School administrator panel to manage teacher accounts
- School-wide book restriction tools
- Pre-populated with 20,000+ UK state-funded primary schools (via government URN dataset)

## Tech Stack
- Backend: Laravel, Eloquent ORM
- Frontend: Blade, Tailwind CSS, JavaScript
- Database: SQLite
- APIs: Google Books API, RoboHash, Internet Archive
- Testing: PHPUnit

Many more features coming in the future!1 :3
