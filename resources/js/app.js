/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

//require('./csrf.js');

/*import { createApp } from 'vue';

const app = createApp({
    components: {
        HeaderComponent
    }
}).mount('#app');
*/
window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

//import router from "./routes/router";

//const app = new Vue({
//    el: '#app',
//    router: router,
//});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
import HeaderComponent from "./components/HeaderComponent";
import ExampleComponent from "./components/ExampleComponent";
import DateComponent from "./components/DateComponent";
import Vue from 'vue';
import Vuetify from 'vuetify';
import 'vuetify/dist/vuetify.min.css';
import "@mdi/font/css/materialdesignicons.css";
import 'material-design-icons-iconfont/dist/material-design-icons.css';


import '@mdi/font/css/materialdesignicons.css';
import 'material-design-icons-iconfont/dist/material-design-icons.css';
import axios from "axios";

//Vue.use(Vuetify);
//Vue.component('example-component', require('./components/ExampleComponent.vue').default);
//new Vue({
//    el: '#login',
//    vuetify: new Vuetify(),
//});
//import router from './router';

//import App from './App.vue';


Vue.use(Vuetify);

Vue.component('header-component', require('./components/HeaderComponent.vue').default);
Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('date-component', require('./components/DateComponent.vue').default);

Vue.component('v-select', require('vue-select').default);
//import vuetify from './plugins/vuetify';

new Vue({
    el: '#app',
    vuetify: new Vuetify(),
            data: {
                csvFile: null,
                csvErrors: []
            },
            methods: {
                onFileChange(e) {

                    this.csvFile = e.target.files[0];

                },
                onSubmit() {

                    this.csvErrors = [];

                    const url = '/ajax/csv_import';
                    let formData = new FormData();
                    formData.append('csv_file', this.csvFile);
                    axios.post(url, formData)
                        .then(response => {

                            if(response.data.result) {

                                document.getElementById('file').value = '';
                                alert('インポートが完了しました。');

                            }

                        })
                        .catch(error => {

                            this.csvErrors = error.response.data.errors.csv_file;

                        });

                }
            },
    //router,
    iconfont: 'mdi',
    components: { HeaderComponent,ExampleComponent,DateComponent},
    //template: '<App />'
});



