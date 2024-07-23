<script setup>
import { ref, onMounted, reactive, watch, toDisplayString } from "vue";
import { useRouter, useRoute } from 'vue-router';
import { useToastr } from '@/toastr';
import { Form, Field } from 'vee-validate';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/themes/light.css'
import { useAuthUserStore } from "../../stores/AuthUserStore.js";
import { usePostConfigStore } from '../../stores/PostConfigStore';
import { useMasterDataStore } from "../../stores/MasterDataStore.js";

const image_cloud_id = ref('');
const widget = window.cloudinary.createUploadWidget(
    {
        cloud_name: "dezj1x6xp",
        upload_preset: "pandanviewmandeh"
    },
    (error, result) => {
        if (!error && result && result.event === 'success') {
            console.log('Done Uploading...', result.info);
            image_cloud_id.value = result.info.secure_url;
            form.cover = result.info.secure_url;
        }

        if (!error && result && result.event == 'close') {
            // handleFileChange();
        }
    }
)

function openUploadWidget() {
    widget.open();
}


const loading = ref(false);

const masterDataStore = useMasterDataStore();
const postConfigStore = usePostConfigStore();
const authUserStore = useAuthUserStore();
const router = useRouter();
const route = useRoute();
const toastr = useToastr();
const placeholder = ref('Pilih Item');
const form = reactive({
    cover: 'https://res.cloudinary.com/dezj1x6xp/image/upload/v1721710762/PandanViewMandeh/no-image_tbc7ea.png',
    title: '',
    category: 0,
    desc: 0,
    keywords: '',
    meta_desc: '',
    kabkota: 0,
    editor: '',
    photographer: '',
    is_breaking: false,
    is_recommended: false,
    is_featured: false,
    is_slider: false,
});
const editReportUserID = ref('');
const pageCategory = ref(postConfigStore.postconfig.category);

const handleSubmit = (values, actions) => {

    loading.value = true;
    if (editMode.value) {
        editReport(values, actions)
    } else {
        createReport(values, actions);
    }


};

const createReport = (values, actions) => {
    axios.post('/api/posts/create', form)
        .then((response) => {
            router.push('/admin/posts/' + postConfigStore.postconfig.category);
            toastr.success('Tulisan berhasil ditambahkan');
        })
        .catch((error) => {
            actions.setErrors(error.response.data.errors);
        })
        .finally(() => {
            loading.value = false;
        });
};

const editReport = (values, actions) => {
    axios.put(`/api/posts/${route.params.id}/edit`, form)
        .then((response) => {
            router.push('/admin/posts/' + postConfigStore.postconfig.category);
            toastr.success('Tulisan berhasil diubah');
        })
        .catch((error) => {
            actions.setErrors(error.response.data.errors);
        })
        .finally(() => {
            loading.value = false;
        });
};

const editMode = ref(false);
const getPosts = () => {
    axios.get(`/api/posts/${route.params.id}/edit`)
        .then((response) => {
            console.log('response');
            console.log(response);


            // console.log('authUserStore.user.id');
            // console.log(authUserStore.user.id);
            // console.log('response.data.report.user_id');
            // console.log(response.data.report.user_id);

            // if (editMode.value && authUserStore.user.id != response.data.report.user_id) {
            //     router.push('/admin/reports');
            // } else {

            // form = response.data;
            form.cover = response.data.cover;
            form.title = response.data.title;
            form.category = response.data.category_id;
            form.desc = response.data.desc;
            form.keywords = response.data.keywords;
            form.meta_desc = response.data.meta_desc;
            form.kabkota = response.data.id_kabkota;
            form.editor = response.data.editor;
            form.photographer = response.data.photographer;
            form.is_breaking = response.data.is_breaking;
            form.is_recommended = response.data.is_recommended;
            form.is_featured = response.data.is_featured;
            form.is_slider = response.data.is_slider;
            // form.date = response.data.report.date;
            // form.work_name = response.data.work_name;
            // form.work_detail = response.data.work_detail;
            // form.volume = response.data.volume;
            // form.unit = response.data.unit_value;
            // form.evidence = response.data.evidence;
            // form.evidence_url = response.data.evidence_url;
            // }
        });
};

onMounted(() => {

    if (route.name === 'admin.posts.edit') {
        editMode.value = true;
        getPosts();
    }

    postConfigStore.postconfig.category = postConfigStore.postconfig.category == '' ? 'daerah' : postConfigStore.postconfig.category;
    postConfigStore.postconfig.category_id = postConfigStore.postconfig.category_id == 0 ? 2 : postConfigStore.postconfig.category_id;
    
    form.category = postConfigStore.postconfig.category_id;


    console.log('route.name');
    console.log(route.name);

    pageCategory.value = postConfigStore.postconfig.category;
    if (pageCategory.value == 'utama') {
        form.kabkota = 0;
    }

    if(authUserStore.user.current_role_id == 'KONTRIBUTOR_DAERAH') {
        console.log('masuk sini dong');
        form.kabkota = authUserStore.user.kabkota.id_kabkota;
        form.category = 2;
    }


    masterDataStore.getKabkotaList();
    masterDataStore.getCategoryList();

    flatpickr(".flatpickr", {
        dateFormat: "Y-m-d",
        disableMobile: true,
    });

    console.log("MOUNTEDPOSTFORM")
});

watch(() => [form.category], function (category) {
    console.log('category');
    console.log(category);
    if (form.category == 'utama') {
        form.kabkota = 1;
    } 

    if (form.category == 'daerah') {
        postConfigStore.postconfig.category_id = 2;
        postConfigStore.postconfig.category = 'daerah';
    } 
    
});

</script>

<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <span v-if="editMode">Edit Berita </span>
                        <span v-else>Buat Berita&nbsp;</span>
                        <span class="text-sm text-muted">{{ postConfigStore.postconfig.category }} </span>

                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <router-link to="/admin/dashboard">Home</router-link>
                        </li>
                        <li class="breadcrumb-item">
                            <router-link to="/admin/reports">Laporan Kerja</router-link>
                        </li>
                        <li class="breadcrumb-item active">
                            <span v-if="editMode">Edit</span>
                            <span v-else>Buat</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card" style="margin-bottom: 100px">
                        <div class="card-body">
                            <Form @submit="handleSubmit" v-slot:default="{ errors }">
                                <div class="row">


                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="date">Unggah Cover</label>
                                            <div v-if="form.cover">
                                                <!-- <img class="preview-cover" height="auto"
                                    :src="form.cover" alt="User profile picture"> -->

                                                <div class="col-sm-4">
                                                    <div class="position-relative">
                                                        <img :src="form.cover" alt="Photo 2" class="img-fluid">
                                                        <!-- style="height: 200px !important;" -->
                                                        <div class="ribbon-wrapper ">
                                                            <div class="ribbon bg-warning">
                                                                Preview
                                                            </div>
                                                        </div>
                                                        <br>
                                                        <button @click="openUploadWidget" type="button"
                                                            id="cover_image_url_btn"
                                                            class="btn btn-secondary btn-sm float-right mt-2 mb-5 btn-block">Unggah Gambar</button>
                                                    </div>
                                                </div>

                                            </div>
                                            <div v-else>
                                                <button @click="openUploadWidget" type="button" id="cover_image_url_btn"
                                                    class="btn btn-secondary btn-sm">Unggah Cover</button>
                                            </div>
                                            <div class="form-group">
                                                <span class="invalid-feedback">{{ errors.cover }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group mb-3 row">
                                            <label class="title" for="highlights">Highlights</label>

                                            <div class="col-sm-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        v-model="form.is_featured" id="is_featured" name="is_featured"
                                                        :true-value="1" :false-value="0">
                                                    <label class="form-check-label" for="is_featured">
                                                        Featured
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-sm-3">

                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        v-model="form.is_recommended" id="is_recommended"
                                                        name="is_recommended" :true-value="1" :false-value="0">
                                                    <label class="form-check-label" for="is_recommended">
                                                        Recommended
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">

                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        v-model="form.is_breaking" id="is_breaking" name="is_breaking"
                                                        :true-value="1" :false-value="0">
                                                    <label class="form-check-label" for="is_breaking">
                                                        Breaking News
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="col-sm-3">

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            v-model="form.is_slider" id="is_slider" name="is_slider"
                                                            :true-value="1" :false-value="0">
                                                        <label class="form-check-label" for="is_slider">
                                                            Slider
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="title">Judul Tulisan</label>
                                            <input v-model="form.title" type="text" class="form-control form-control-sm"
                                                :class="{ 'is-invalid': errors.title }" id="title"
                                                placeholder="Judul...">
                                            <span class="invalid-feedback">{{ errors.title }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="desc">Konten Tulisan</label>
                                    <SummernoteEditor v-model="form.desc" />
                                    <span class="invalid-feedback">{{ errors.desc }}</span>
                                </div>




                                <div class="form-group" v-if="route.name === 'admin.posts.edit' || authUserStore.user.current_role_id != 'KONTRIBUTOR_DAERAH'">
                                    <label for="client">Kategori</label>
                                    <select v-model="form.category" class="custom-select rounded-0 form-control-sm form-control" :disabled="authUserStore.user.current_role_id == 'KONTRIBUTOR_DAERAH'"
                                        :placeholder="placeholder">
                                        <option disabled value="" class="text-muted">- Pilih Kategori -</option>
                                        <option v-for="opt in masterDataStore.categoryList" :value="opt.id"
                                            :key="opt.id">{{
                            opt.text }}
                                        </option>
                                    </select>
                                    <span class="invalid-feedback">{{ errors.unit }}</span>
                                </div>

                                <div class="form-group" v-else>
                                    <label class="title" for="category">Kategori {{ postConfigStore.postconfig.category }}</label>
                                    <input class="form-control form-control-sm" type="text"
                                        v-model="postConfigStore.postconfig.category" disabled>
                                    <input type="hidden" name="category" id="category" :v-model="form.category">
                                    <span class="invalid-feedback">{{ errors.volume }}</span>
                                </div>




                                <!-- <div class="form-group" v-if="pageCategory == 'utama'">
                                    <input type="hidden" name="kabkota" id="kabkota" :v-model="form.kabkota"> -->

                                <!-- <label for="client">Daerah Kab/Kota</label>
                                    <select v-model="form.kabkota" class="custom-select rounded-0 form-control-sm form-control"
                                        :placeholder="placeholder" disabled>
                                        <option disabled value="" class="text-muted">- Pilih daerah -</option>
                                        <option v-for="opt in masterDataStore.kabkotaList" :value="opt.id"
                                            :key="opt.id">{{
                            opt.text }}
                                        </option>
                                    </select>
                                    <span class="invalid-feedback">{{ errors.unit }}</span> -->
                                <!-- </div> -->

                                <div class="form-group" v-if="authUserStore.user.current_role_id != 'KONTRIBUTOR_DAERAH'">
                                    <label for="client">Daerah Kab/Kota</label>
                                    <select v-model="form.kabkota" class="custom-select rounded-0 form-control-sm form-control"
                                        :placeholder="placeholder">
                                        <option disabled value="" class="text-muted">- Pilih daerah -</option>
                                        <option v-for="opt in masterDataStore.kabkotaList" :value="opt.id"
                                            :key="opt.id">{{
                            opt.text }}
                                        </option>
                                    </select>
                                    <span class="invalid-feedback">{{ errors.unit }}</span>
                                </div>

                                <div class="form-group" v-else>
                                    <label for="client">Daerah Kab/Kota</label>
                                    <select v-model="form.kabkota" class="custom-select rounded-0 form-control-sm form-control"
                                        :placeholder="placeholder" disabled>
                                        <option disabled value="" class="text-muted">- Pilih daerah -</option>
                                        <option v-for="opt in masterDataStore.kabkotaList" :value="opt.id"
                                            :key="opt.id">{{
                            opt.text }}
                                        </option>
                                    </select>
                                    <span class="invalid-feedback">{{ errors.unit }}</span>
                                </div>

                                <div class="form-group">
                                    <label for="keywords">Kata Kunci</label>
                                    <input v-model="form.keywords" type="text" class="form-control form-control-sm"
                                        :class="{ 'is-invalid': errors.keywords }" id="keywords"
                                        placeholder="Kata Kunci dipisahkan dengan Koma">
                                    <span class="invalid-feedback">{{ errors.keywords }}</span>
                                </div>

                                <div class="form-group">
                                    <label for="meta_desc">Deksripsi Meta (Opsional)</label>
                                    <textarea v-model="form.meta_desc" class="form-control form-control-sm"
                                        :class="{ 'is-invalid': errors.meta_desc }" id="meta_desc" rows="3"
                                        placeholder="Deksripsi Meta"></textarea>
                                    <span class="invalid-feedback">{{ errors.meta_desc }}</span>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="editor">Editor</label>
                                            <input v-model="form.editor" type="text" class="form-control form-control-sm"
                                                :class="{ 'is-invalid': errors.editor }" id="editor"
                                                placeholder="Editor">
                                            <span class="invalid-feedback">{{ errors.editor }}</span>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="photographer">Fotografer</label>
                                            <input v-model="form.photographer" type="text" class="form-control form-control-sm"
                                                :class="{ 'is-invalid': errors.photographer }" id="photographer"
                                                placeholder="Fotografer">
                                            <span class="invalid-feedback">{{ errors.photographer }}</span>
                                        </div>
                                    </div>
                                </div>



                                <button type="submit" class="btn btn-primary btn-block btn-sm" :disabled="loading">
                                    <div v-if="loading" class="spinner-border spinner-border-sm mr-2" role="status">
                                        <span class="sr-only ">Loading...</span>
                                    </div>
                                    <span v-else>Submit</span>
                                </button>
                            </Form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
html {
    zoom: 0.8 !important;
    /* Old IE only */
    -moz-transform: scale(0.8) !important;
    -webkit-transform: scale(0.8) !important;
    transform: scale(0.8) !important;
}
</style>