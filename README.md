## Fhir based Patient manager, no database required.

Developed by Servicio de Salud Iquique.

Installation:

- ```git clone https://github.com/cl-ssi/unisalud```
- ```cd unisalud```
- ```composer install```
- ```cp .env.example .env```
- ```php artisan key:generate```
- Edit file .env and set FHIR_URL_BASE variable with server fhir base path.
- ```php artisan serve```
- Navigate to http://localhost:8000/patient

## Dependencies
- Requires having the php soap extension enabled `extension=soap`.
- Build on laravel 8.24 + bootstrap + livewire


## License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
