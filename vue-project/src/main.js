import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import { createPinia } from "pinia";
import Chart from 'primevue/chart';
 
import ProgressBar from 'primevue/chart';
 

import PrimeVue from "primevue/config";
import ToastService from 'primevue/toastservice'; // <-- 1. Impor ini
import Aura from '@primeuix/themes/aura';

// PrimeVue components
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Dialog from "primevue/dialog";
import Textarea from "primevue/textarea";
import Select from 'primevue/select';
import Tag from "primevue/tag";
import ConfirmationService from 'primevue/confirmationservice'
import AutoComplete from 'primevue/autocomplete';


 
import "primeicons/primeicons.css";
// import 'primeflex/primeflex.css'
import ConfirmDialog from 'primevue/confirmdialog'

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.use(PrimeVue, {
    // Default theme configuration
    theme: {
        preset: Aura,
        options: {
            prefix: 'p',
            darkModeSelector: '.none',
            cssLayer: false,
            
        }
    }
 });

app.component("Button", Button);
app.component("DataTable", DataTable);
app.component("Column", Column);
app.component("Dialog", Dialog);
app.component("InputText", InputText);
app.component("InputNumber", InputNumber);
app.component("Textarea", Textarea);
app.component("Select", Select);
app.component("Tag", Tag);
app.component("AutoComplete", AutoComplete);
 
app.component('Chart', Chart);
app.component('ProgressBar', ProgressBar)
app.use(ToastService); // <-- 2. WAJIB daftarkan ini sebelum .mount()
app.component('ConfirmDialog', ConfirmDialog)



app.use(ConfirmationService)


 


app.mount("#app");