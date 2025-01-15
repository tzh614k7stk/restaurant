## Notice
The project serves as a showcase of one's abilities to implement a solution on schedule.<br>
It took exactly 7 days to complete and is never going to be worked on again.

## Environment Variables
- `APP_DOMAIN=<your domain>` (used for vite hmr, if APP_URL is `http://example.com`, APP_DOMAIN would be `example.com`)

## Features
- responsive design
- auth (login/register/logout)
- guest/user/employee/admin roles
- reservations (create/cancel/display)
  - related to dining tables, opening hours and other reservation options
- ability to have a regular opening hours schedule with special opening hours for specific days
- employee panel
  - manage and lookup reservations
  - manage and lookup users
  - manage opening hours
  - manage dining tables
  - manage reservation options
  - management of employees for admins
  - other configuration
- all api call parameters are properly validated on the server and most of them on the client as well
- and more..

## Notes
- all dates and opening hours operate on a defined timezone
- opening hours going over midnight must have the opening time be larger than the closing time
  - 14:00 - 01:30 (next day) is ok
  - 07:00 - 06:30 (next day) is ok
  - 07:00 - 08:00 (next day) is not ok
  - 05:30 - 06:00 (next day) is not ok
- nonstop opening hours are not directly supported
  - it is possible to have each day open from 00:00 to 24:00 but it will not be possible to have a reservation that crosses midnight
- the database seeder contains sample data, do not use in production

## Real World Deployment Recommendations
- add captcha for auth (login/register)
- add rate limiting
- add mail server
  - registration confirmation
  - password reset
  - reservation confirmation
  - reservation reminder
  - reservation cancellation

## Possible Improvements
- if visitor is not logged in and attempts to create a reservation, save reservation details and restore them after login
- use laravel broadcasting for real time updates on reservations and related data
- load reservations and related data in chunks instead of all at once
- allow employees to create reservations for people who call in but do not have an account
  - currently could be achieved by creating a palceholder account for all the people who call in and are not registered while adding note to the reservation with the name of the person and their contact information
  - in case the reservation must be cancelled, this may pose an issue as all the people who are not registered would have to be contacted manually to be informed
- any sort of changes to opening hours should trigger an action to deal with reservations, which it affects (same with adding custom opening hours or closed days)
- deleting tables that are used in reservations is prohibited as doing so would cause cascade deletion of affected reservations and since currently no mail server has been set up, the guests would not be informed
- adding an option for users to view dates and times in either restaurant timezone or their own timezone

## Time Constraints
As already mentioned above, there are parts of the project, which should be implemented before a production rollout.<br>
For this reason, some of the code is not optimized for performance/readability/maintainability.

## Technologies
- laravel
- alpine.js
- tailwindcss
- mysql