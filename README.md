# WeClaim V1

## Overview
WeClaim V1 is a proprietary claiming system developed for internal use at Wegrow Global Sdn. Bhd. This application streamlines the process of submitting, reviewing, and approving expense claims for staff members across the organization.

## Features
- Multi-step claim submission with location tracking
- Document management for supporting evidence
- Multi-level approval workflow (Admin, HR, Finance, Director)
- Automated email notifications
- Exportable reports (Excel, Word, PDF)
- User management and role-based access control
- Mobile-responsive interface

## Tech Stack
- Backend: Laravel PHP Framework
- Frontend: Blade templates with Livewire components
- Database: MySQL
- Authentication: Laravel Fortify
- CSS Framework: Tailwind CSS
- JS Libraries: Alpine.js

## Development Setup
1. Clone the repository
```bash
git clone [repository URL]
cd WeClaim-V1
```

2. Install dependencies
```bash
composer install
npm install
```

3. Environment configuration
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your database in the `.env` file
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=weclaim
DB_USERNAME=root
DB_PASSWORD=
```

5. Run migrations and seeders
```bash
php artisan migrate --seed
```

6. Start the development server
```bash
php artisan serve
npm run dev
```

## Documentation
For detailed information about the project, please refer to:
- [Architecture Documentation](docs/architecture.md) - System architecture and component relationships
- [Technical Documentation](docs/technical.md) - Technical specifications and established patterns
- [Task Documentation](tasks/tasks.md) - Current development tasks and requirements
- [Status Documentation](docs/status.md) - Project status, progress, and known issues

## Important Notice
**PRIVATE USE ONLY**: While this repository is publicly visible, the code is intended exclusively for use within Wegrow Global Sdn. Bhd. This is not open source software and is not available for public use, distribution, or modification by external parties.

## Usage
Usage guidelines and documentation are available to authorized company personnel in our internal documentation portal.

## Permissions
This codebase is proprietary to Wegrow Global Sdn. Bhd. The code is made publicly visible for transparency and collaboration purposes with our stakeholders, but its use is restricted to authorized personnel only.

## License
This project is licensed under the terms of the license included in this repository. See the [LICENSE](LICENSE.MD) file for details. The license restricts usage to Wegrow Global Sdn. Bhd. only.

## Contact
For inquiries about this repository, please contact our development team at dev@wegrowglobal.com.

© 2024 Wegrow Global Sdn. Bhd. All rights reserved.
