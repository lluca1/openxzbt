## Project Info

🕒 Made in **48 hours** for [UniHack 2025](https://unihack.eu) as **Team EREG**  
🔗 Showcase / Tutorial: [Watch on YouTube](https://www.youtube.com/watch?v=BrlwSAzDLOw)

---

# 🧩 openxzbt

openxzbt is an open-source platform for creating and exploring interactive 3D expositions.
It combines a Laravel Breeze + Livewire web app with a Unity 3D environment editor.

## Tech Stack

- Backend: Laravel, Livewire, Breeze
- Frontend: Tailwind CSS, Vite, NPM
- Database: MySQL
- 3D Client: Unity
- Deployment: Laravel Forge

## Repository Structure

forge → Laravel web app (forum, auth, exposition management)
game-dev → Unity 3D project (exposition editor and viewer)

## Setup
Laravel App (forge branch)

git checkout forge
cd laravel-app
cp .env.example .env
composer install
npm install && npm run build
php artisan migrate --seed
php artisan serve

Unity Project (game-dev branch)

git checkout game-dev
Then open the unity-project folder in Unity (202x or later).

## Overview

Users create and manage expositions via the web app.
Expositions can be viewed and edited in Unity as 3D environments.
The web and Unity components share a unified data layer.

## License

GPL-3.0 License © 2025 openxzbt
