import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Registrar el plugin Collapse
Alpine.plugin(collapse);

window.Alpine = Alpine;
Alpine.start();