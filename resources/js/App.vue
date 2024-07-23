<script setup>
import { useRouter, useRoute } from 'vue-router';
import { ref, onMounted, computed, watch } from 'vue';
import AppNavbar from './components/AppNavbar.vue';
import SidebarLeft from './components/SidebarLeft.vue';
import SidebarRight from './components/SidebarRight.vue';
import AppFooter from './components/AppFooter.vue';
import { useAuthUserStore } from './stores/AuthUserStore';
import { useSettingStore } from './stores/SettingStore';
import { useScreenDisplayStore } from './stores/ScreenDisplayStore.js';


const route = useRoute();
const router = useRouter();
const screenDisplayStore = useScreenDisplayStore();
const deferredPrompt = ref(null);
const authUserStore = useAuthUserStore();
const settingStore = useSettingStore();

const logout = () => {
    axios.post('/logout')
        .then((response) => {
            authUserStore.user.name = '';
            router.push('/login');
            localStorage.clear();
            // getActivePinia()._s.forEach(store => store.$reset());
        });
};

const currentThemeMode = computed(() => {
    return settingStore.theme === 'dark' ? 'dark-mode' : '';
});

const dismiss = async () => {
    deferredPrompt.value = null;
}
const install = async () => {
    deferredPrompt.prompt();
}

watch(() => [authUserStore.user.name, route.name], function () {

    if (authUserStore.user.name == '') {
        router.push('/login');
    }
    console.log('value changes detected');
    console.log(route.name);

});

const backbuttonHandler = () => {
    navigator.app.exitApp() 
};

onMounted(() => {

    screenDisplayStore.getScreenSize();
    window.addEventListener("beforeinstallprompt", e => {
        e.preventDefault();
        // Stash the event so it can be triggered later.
        deferredPrompt.value = e;
    });
    window.addEventListener("appinstalled", () => {
        deferredPrompt.value = null;
    });
    window.addEventListener('resize', screenDisplayStore.toggleIsMobile);

    window.onpopstate = event => {
        console.log('back button');
        navigator.app.exitApp() 
        // if (route.path == "/login") {
        //     router.push("/admin/dashboard"); // redirect to home, for example
        // }
    };

    document.addEventListener('backbutton', backbuttonHandler, false);

});
</script>

<template>
    <!-- <v-app>
        <v-banner  color="info" class="text-left">
            Get our free app. It won't take up space on your phone and also works offline!

            <template v-slot:actions>
                <v-btn text @click="dismiss">Dismiss</v-btn>
                <v-btn text @click="install">Install</v-btn>
            </template>
        </v-banner>
        
    </v-app> -->

    <div v-if="authUserStore.user.name !== ''" class="wrapper" :class="currentThemeMode" id="app">


        <v-app app>

            <AppNavbar  />
            <SidebarLeft />
            <div class="content-wrapper">
                <router-view :key="route.fullPath"></router-view>
                <!-- <router-view></router-view> -->

            </div>
            <SidebarRight />
            <AppFooter />

            <v-bottom-navigation grow v-if="screenDisplayStore.isMobile">
                <v-btn value="dashboard" to="/admin/dashboard">
                    <v-icon>mdi-home</v-icon>

                    <span>Beranda</span>
                </v-btn>

                <v-btn value="reports" to="/admin/reports">
                    <v-icon>mdi-book</v-icon>

                    <span>Laporan</span>
                </v-btn>

                <v-btn value="monitor" to="/admin/org-reports"
                    v-if="authUserStore.user.role == 'SUPERADMIN' || authUserStore.user.role == 'ADMIN' || authUserStore.user.role == 'REVIEWER'">
                    <v-icon>mdi-monitor</v-icon>

                    <span>Monitor</span>
                </v-btn>

                <v-btn value="profile" to="/admin/profile">
                    <v-icon>mdi-account</v-icon>

                    <span>Profil</span>
                </v-btn>

                <!-- <v-btn value="logout" @click.prevent="logout">
                    <v-icon>mdi-logout</v-icon>

                    <span>Logout</span>
                </v-btn> -->
            </v-bottom-navigation>

            <VLayoutItem model-value position="bottom" class="text-end" size="88"
                v-if="screenDisplayStore.isMobile && authUserStore.user.name !== '' && route.name == 'admin.reports'">
                <div class="ma-4">
                    <VBtn to="/admin/reports/create" icon="mdi-plus" size="large" color="primary" elevation="8" />
                </div>
            </VLayoutItem>
        </v-app>
    </div>
    <div v-else class="login-page" :class="currentThemeMode">
        <router-view></router-view>
    </div>
</template>