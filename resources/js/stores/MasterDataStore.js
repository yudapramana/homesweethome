import axios from 'axios';
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useLoadingStore } from "./LoadingStore";
import { useMonthYearStore } from "./MonthYearStore.js";
import { useStorage } from '@vueuse/core';
import { useRouter, useRoute } from 'vue-router';
import { useAuthUserStore } from "./AuthUserStore.js";

export const useMasterDataStore = defineStore('MasterDataStore', () => {
    const orgId = useStorage('MasterDataStore:orgId', ref(''));
    const userId = useStorage('MasterDataStore:userId', ref(''));

    const orgList = useStorage('MasterDataStore:orgList', ref([]));
    const userList = useStorage('MasterDataStore:userList', ref({}));
    const categoryList = useStorage('MasterDataStore:categoryList', ref([]));
    const kabkotaList = useStorage('MasterDataStore:kabkotaList', ref([]));


    const loadingStore = useLoadingStore();
    const router = useRouter();
    const authUserStore = useAuthUserStore();

    const getCategoryList = async () => {


        if (kabkotaList.value.length == 0) {
            loadingStore.toggleLoading();
            await axios.get('/api/master', {
                params: {
                    type: 'category',
                }
            })
                .then((response) => {
                    console.log("categorylistresponse");
                    console.log(response);
                    categoryList.value = response.data.data;
                    loadingStore.toggleLoading();
                }).catch((error) => {
                    loadingStore.toggleLoading();

                    // console.log(error.response.data)
                    if (error.response.status === 401) {

                        authUserStore.user.name = '';
                        router.push('/login');
                        localStorage.clear();

                    }
                });
        }


    };

    const getKabkotaList = async () => {


        if (kabkotaList.value.length == 0) {
            loadingStore.toggleLoading();
            await axios.get('/api/master', {
                params: {
                    type: 'kabkota',
                }
            })
                .then((response) => {
                    console.log("kabkotalistresponse");
                    console.log(response);
                    kabkotaList.value = response.data.data;
                    loadingStore.toggleLoading();
                }).catch((error) => {
                    loadingStore.toggleLoading();

                    // console.log(error.response.data)
                    if (error.response.status === 401) {

                        authUserStore.user.name = '';
                        router.push('/login');
                        localStorage.clear();

                    }
                });
        }


    };

    const getOrgList = async () => {

        // console.log('orgList.value.length');
        // console.log(orgList.value.length);

        if (orgList.value.length == 0) {
            loadingStore.toggleLoading();
            await axios.get('/api/master', {
                params: {
                    type: 'orgs',
                }
            })
                .then((response) => {
                    orgList.value = response.data.data;
                    loadingStore.toggleLoading();
                }).catch((error) => {
                    loadingStore.toggleLoading();

                    // console.log(error.response.data)
                    if (error.response.status === 401) {

                        authUserStore.user.name = '';
                        router.push('/login');
                        localStorage.clear();

                    }
                });
        }


    };

    const getUserList = async (org) => {
        // console.log('orgId');
        // console.log(org);

        loadingStore.toggleLoading();
        await axios.get('/api/master', {
            params: {
                type: 'users',
                id: org
            }
        })
            .then((response) => {
                userList.value = response.data.data;
                loadingStore.toggleLoading();
            }).catch((error) => {
                loadingStore.toggleLoading();
                // console.log(error.response.data)
                if (error.response.status === 401) {

                    authUserStore.user.name = '';
                    router.push('/login');
                    localStorage.clear();

                }
            });
    };

    const getUserListbyOrgID = async (orgId) => {
        // console.log('orgId');

        loadingStore.toggleLoading();
        await axios.get('/api/master', {
            params: {
                type: 'users',
                id: orgId
            }
        })
            .then((response) => {
                userList.value = response.data.data;
                loadingStore.toggleLoading();
            }).catch((error) => {
                loadingStore.toggleLoading();
                // console.log(error.response.data)
                if (error.response.status === 401) {

                    authUserStore.user.name = '';
                    router.push('/login');
                    localStorage.clear();

                }
            });
    };

    return { orgId, userId, orgList, userList, kabkotaList, categoryList,  getOrgList, getUserList, getUserListbyOrgID, getKabkotaList, getCategoryList };
});