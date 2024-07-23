<script setup>
import { useRouter } from 'vue-router';
import { useAuthUserStore } from '../stores/AuthUserStore';
import { useSettingStore } from '../stores/SettingStore';
import CloudImage from '../components/CloudImage.vue';
import { getActivePinia } from "pinia"
import { usePostConfigStore } from '../stores/PostConfigStore';
import { useMasterDataStore } from "../stores/MasterDataStore.js";




const router = useRouter();
const settingStore = useSettingStore();
const authUserStore = useAuthUserStore();
const postConfigStore = usePostConfigStore();
const masterDataStore = useMasterDataStore();



const logout = () => {
    axios.post('/logout')
        .then((response) => {
            authUserStore.user.name = '';
            router.push('/login');
            localStorage.clear();
            // getActivePinia()._s.forEach(store => store.$reset());
        });
};

const setPostConfig = (category, category_id) => {
    postConfigStore.postconfig.category = category;
    postConfigStore.postconfig.category_id = category_id;

    console.log('postConfigStore.postconfig');
    console.log(postConfigStore.postconfig.category);
    console.log(postConfigStore.postconfig.category == 'utama');

    postConfigStore.postconfig.dtloading = true;
    console.log('postConfigStore.postconfig.dtloading');
    console.log(postConfigStore.postconfig.dtloading);
}

</script>
<template>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">

        <a href="#" class="brand-link">
            <img src="/app_logo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">{{ settingStore.setting.app_name }}</span>
        </a>

        <div class="sidebar">

            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <!-- <CloudImage :image-name="authUserStore.user.avatar" /> -->

                    <img :src="authUserStore.user.avatar" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block" style="font-size: small;">{{ authUserStore.user.name }}</a>
                </div>
            </div>



            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false">


                    <li class="nav-item">
                        <router-link to="/admin/dashboard" active-class="active" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>
                                Beranda
                            </p>
                        </router-link>
                    </li>

                    <li class="nav-header">BERITA</li>

                    <template v-if="authUserStore.user.current_role_id != 'KONTRIBUTOR_DAERAH'">
                        <li class="nav-item" v-for="opt in masterDataStore.categoryList" :value="opt.id" :key="opt.id">
                            <router-link :to="`/admin/posts/${opt.slug}`" active-class="active"
                                @click="setPostConfig(opt.slug, opt.id)" class="nav-link">
                                <i class="nav-icon far fa-circle nav-icon"></i>
                                <p>
                                    {{ opt.text }}
                                </p>
                            </router-link>
                        </li>
                    </template>

                    <template v-else>
                        <li class="nav-item">
                            <router-link to="/admin/posts/daerah" active-class="active" @click="setPostConfig('daerah')"
                                class="nav-link">
                                <i class="nav-icon fas fa-book"></i>
                                <p>
                                    Utama Daerah
                                </p>
                            </router-link>
                        </li>
                    </template>






                    <li class="nav-header">KELOLA</li>

                    <li class="nav-item">
                        <router-link to="/admin/profile" active-class="active" class="nav-link">
                            <i class="nav-icon fas fa-user"></i>
                            <p>
                                Profil
                            </p>
                        </router-link>
                    </li>

                    <li class="nav-item" v-if="authUserStore.user.current_role_id == 'SUPERADMIN'">
                        <router-link to="/admin/organizations" active-class="active" class="nav-link">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>
                                Master Daerah
                            </p>
                        </router-link>
                    </li>
                    <li class="nav-item"
                        v-if="authUserStore.user.current_role_id == 'SUPERADMIN' || authUserStore.user.current_role_id == 'ADMIN'">
                        <router-link to="/admin/users" active-class="active" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>
                                Pengguna
                            </p>
                        </router-link>
                    </li>
                    <li class="nav-item" v-if="authUserStore.user.current_role_id == 'SUPERADMIN'">
                        <router-link to="/admin/settings" active-class="active" class="nav-link">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>
                                Pengaturan
                            </p>
                        </router-link>
                    </li>

                    <li class="nav-item">
                        <form class="nav-link">
                            <a href="#" @click.prevent="logout">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>
                                    Keluar
                                </p>
                            </a>
                        </form>

                    </li>
                </ul>
            </nav>

        </div>

    </aside>
</template>