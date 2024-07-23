<script setup>
import axios from "axios";
import { ref, onMounted, reactive, watch, toDisplayString } from "vue";
import { formatDate, formatDateString, formatDateStringHuman } from '../../helper.js';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import Swal from 'sweetalert2';
import moment from 'moment';
import { useStorage } from '@vueuse/core';
import { useAuthUserStore } from "../../stores/AuthUserStore.js";
import { useLoadingStore } from "../../stores/LoadingStore.js";
import { useScreenDisplayStore } from '../../stores/ScreenDisplayStore.js';
import ButtonDT from '../../components/ButtonDT.vue';
import { debounce } from 'lodash';
import { usePostConfigStore } from '../../stores/PostConfigStore';
import { useRouter } from 'vue-router';
import { useToastr } from '@/toastr';

import JlDatatable from 'jl-datatable';
import DataTablesLib from 'datatables.net';
// import DataTablesLib from 'datatables.net-bs4';
import DataTable from 'datatables.net-vue3';
import 'datatables.net-responsive';
import 'datatables.net-bs4';
import { useRoute } from 'vue-router';
const toastr = useToastr();

DataTable.use(DataTablesLib);

const screenDisplayStore = useScreenDisplayStore();
const loadingStore = useLoadingStore();
const authUserStore = useAuthUserStore();
const postConfigStore = usePostConfigStore();
const dtLoading = ref(postConfigStore.postconfig.dtloading);
const route = useRoute();
const router = useRouter();


// For DT Status

const statusArr = ref(['published', 'draft', 'archived']);
const colorArr = ref(['success', 'warning', 'danger']);

// End for DT Status


const posts = ref({ 'data': [] });

const columns = ref([
    {
        data: 'DT_RowIndex',
        width: '3%',
    },
    { data: 'date_add', width: '10%' },
    {
        data: 'cover_small',
        render: function (data, type, row, meta) {
            return `<img class="profile-edit" id="profile_photo_jst" src="${row.cover_small || 'https://artsmidnorthcoast.com/wp-content/uploads/2014/05/no-image-available-icon-6-300x188.png'}" alt="None">`
        }
    },
    {
        data: 'title',
        render: function (data, type, row, meta) {
            return `<b style="larger">${row.title}</b> <br> <span class="text-sm text-muted">Editor: ${row.editor || '-'} &nbsp;&nbsp;&nbsp; Fotografer: ${row.photographer || '-'} </span>`
            // row.title + '<br> Editor: ' + row.editor;
        }
    },
    { data: 'category.title' },
    { data: 'user.name' },
])

const dtOptions = ref({
    pagingType: 'simple_numbers',
    // scrollX: true,
    autoWidth: false,
    processing: true,
    serverSide: true,
    select: true,
    // ajax: "/api/posts",
    ajax: {
        url: '/api/posts',
        data: {
            category: route.params.category
        }
    },
    // data: posts.data,
    ordering: false,
    columnDefs: [
        {
            targets: [0, 2, 4, 5, 6, 7],
            className: 'text-center'
        },
        {
            targets: 2,
            className: 'profile-edit-container'
        },
        {
            targets: 3,
            className: 'smaller-font'
        },
        {
            targets: [0, 1, 4, 5, 6, 7],
            className: 'xsmaller-font'
        }
    ],
    dom: 'tr<"row mt-2"<"col-md-6"i><"col-md-6"p>>'
})

const editMode = ref(false);
const getPosts = async (page = 1) => {
    await axios.get(`/api/posts?page=${page}`)
        .then((response) => {
            console.log(response)
            // console.log(response.data);
            posts.value = response.data;
        })
        .catch((error) => {
            // console.log(error.response.data)
            if (error.response.status === 401) {
                authUserStore.user.name = '';
                router.push('/login');
                localStorage.clear();

            }
        });
}

const editPost = (rowData) => {
    // alert(`Edit ${rowData.id}`);
    router.push(`/admin/posts/${rowData.id}/edit`);

}

const DeletePost = (rowData) => {
    alert(`Delete ${rowData.id}`);

}

const dt = ref();
const table = ref();

const reloadDT = () => {
    // console.log('this');
    // console.log(ref.table);
    // const componentTable = this.$refs.table;
    // componentTable.ajax.url('api/posts').load();
    // dt.ajax.reload();
}

const changePostStatus = async (rowData, status) => {

    Swal.fire({
        title: "Masukkan password akun Anda",
        input: "password",
        inputAttributes: {
            autocapitalize: "off"
        },
        showCancelButton: true,
        confirmButtonText: "Submit",
        showLoaderOnConfirm: true,
        preConfirm: async (login) => {

            await axios.post('/api/post/status/switch', {
                switch_status: true,
                id_post: rowData.id,
                string_status: status,
                password: login
            })
                .then((response) => {
                    console.log(response);
                    dt.value.ajax.reload();
                    if (response.data.success == true) {
                        toastr.success('Status tulisan berhasil diubah');
                    } else {
                        toastr.error('Status tidak berhasil diubah');
                    }
                })
                .catch((error) => {
                    if (error.response.data === 401) {
                        authUserStore.user.name = '';
                        router.push('/login');
                        localStorage.clear();

                    }
                });

        },
        allowOutsideClick: () => !Swal.isLoading()
    });

    // await axios.post('/api/post/status/switch', {
    //     switch_status: true,
    //     id_post: rowData.id,
    //     string_status: status
    // })
    //     .then((response) => {
    //         console.log(response);
    //         dt.value.ajax.reload();
    //         toastr.success('Status tulisan berhasil diubah');
    //         // console.log(response.data);
    //         // posts.value = response.data;
    //     })
    //     .catch((error) => {
    //         // console.log(error.response.data)
    //         if (error.response.status === 401) {
    //             authUserStore.user.name = '';
    //             router.push('/login');
    //             localStorage.clear();

    //         }
    //     });

    // alert('rowData.id: ' + rowData.id + '     | Status: ' + status);

}

onMounted(() => {

    if (!postConfigStore.postconfig.category) {
        router.push('/admin/dashboard');
    }
    console.log('authUserStore.user.current_role_id');
    console.log(authUserStore.user.current_role_id == 'KONTRIBUTOR_DAERAH');

    if(authUserStore.user.current_role_id == 'KONTRIBUTOR_DAERAH') {
        console.log('masuk sini dong');
        router.push('/admin/posts/daerah');
    }

    console.log(" route.params ");
    console.log(route.query.category);


    console.log('table');
    // dt.value = table.value.dt;
    dt.value = table.value.dt;



});
const searchQuery = ref(null);
const categoryParam = ref(route.params.category);
watch([searchQuery, categoryParam], debounce(() => {
    // search();
    console.log(searchQuery);
    dt.value.search(searchQuery.value).draw();

    console.log('dtloadingaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
    // console.log(dtloading);

    dt.value.ajax.reload();
    postConfigStore.postconfig.dtloading = false;
}, 300), { deep: true });
</script>


<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Daftar Berita <span class="text-sm text-muted"> {{ route.params.category }} </span>
                    </h1>
                </div>
                <div class="col-sm-6" v-if="!screenDisplayStore.isMobile">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item">Daftar Berita</li>
                        <li class="breadcrumb-item active">{{ route.params.category }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            <div class="d-flex justify-content-between">
                <div class="d-flex">
                    <!-- <button type="button" class="mb-2 btn btn-primary">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Tambah Satker
                    </button> -->

                    <router-link to="/admin/posts/create">
                        <button class="mb-2 btn btn-primary btn-sm">
                            <i class="fa fa-plus-circle mr-1"></i>
                            Tambah
                        </button>
                    </router-link>
                </div>

                <div>
                    <input type="text" v-model="searchQuery" class="form-control form-control-sm"
                        placeholder="Search...">
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">

                    <!-- <h1>Simple table</h1>
                    <button @click="reloadDT">Change It</button> -->

                    <div class="card">
                        <div class="card-body">
                            <!-- ajax="/api/posts" -->

                            <div class="row">
                                <div class="col-lg-12 d-flex flex-column">
                                    <DataTable :columns="columns" :options="dtOptions" ref="table" id="datatable"
                                        class="table table-bordered display cell-border compact stripe table-responsive">
                                        <thead>
                                            <tr>
                                                <th scope="col text-sm">No</th>
                                                <th scope="col text-sm">Tanggal</th>
                                                <th scope="col text-sm" class="profile-edit-container">Cover</th>
                                                <th scope="col text-sm">Judul</th>
                                                <th scope="col text-sm">Kategori</th>
                                                <th scope="col text-sm">Author</th>
                                                <th scope="col text-sm">Status</th>
                                                <th scope="col text-sm">Actions</th>
                                            </tr>
                                        </thead>

                                        <template #column-6="props">

                                            <div class="btn-group" style="cursor: pointer;">
                                                <!-- class="badge bg-${props.status_array.now_color} dropdown-toggle dropdown-icon" -->

                                                <span
                                                    :class="['badge dropdown-toggle dropdown-icon', 'bg-' + props.rowData.status_array.now_color]"
                                                    data-toggle="dropdown">
                                                    {{ props.rowData.status }} <span class="sr-only"
                                                        style="cursor: pointer;">Toggle Dropdown</span>
                                                </span>
                                                <div class="dropdown-menu" role="menu">
                                                    <a @click="changePostStatus(props.rowData, stat)"
                                                        class="dropdown-item"
                                                        v-for="stat in props.rowData.status_array.status_arr"
                                                        :key="stat" style="cursor: pointer;">{{ stat }}</a>
                                                </div>
                                            </div>
                                            <!-- <button @click="editPost(props.rowData)">
                                        <i class="fa fa-edit text-primary"></i>
                                        {{ props.rowData.id }}
                                    </button> -->
                                        </template>

                                        <template #column-7="props">
                                            <button @click="editPost(props.rowData)" style="font-size: larger;">
                                                <i class="fa fa-edit text-primary"></i>
                                            </button>
                                            <button @click="DeletePost(props.rowData)" style="font-size: larger;"
                                                v-if="authUserStore.user.current_role_id == 'SUPERADMIN'">
                                                <i class="fa fa-trash text-danger ml-2"></i>
                                            </button>
                                        </template>
                                    </DataTable>
                                    <span>&nbsp;</span>
                                    <!-- <Bootstrap4Pagination :data="posts" @pagination-change-page="getPosts" :limit="-1"
                                        :keepLength="true" style="margin-bottom: 100px;" :size="small" :show-disabled="false" /> -->
                                </div>
                            </div>

                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>


</template>

<style>
.dt-search {
    float: right !important;
    margin-bottom: 5px;
}

.profile .profile-edit img {
    max-width: 120px !important;
}

.profile-edit-container {
    max-width: 135px !important;
}

#profile_photo_src {
    max-width: 120px;
}

#preview_cover {
    max-width: 120px !important;
}

.profile-edit {
    max-width: 120px;
}

.form-control {
    display: inline !important;
}

.form-control-sm {
    display: inline !important;
}


.dt-paging {
    float: right !important;
}

.xsmaller-font {
    font-size: x-small !important;
}

.smaller-font {
    font-size: smaller !important;
}

table#datatable td {
    font-size: 1em;
}

th {
    font-size: 14px !important;
}
</style>