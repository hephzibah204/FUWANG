import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

import 'popper.js';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import Swal from 'sweetalert2';
import { marked } from 'marked';

window.Swal = Swal;
window.marked = marked;
