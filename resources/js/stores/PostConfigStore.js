import axios from 'axios';
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useStorage } from '@vueuse/core';


export const usePostConfigStore = defineStore('PostConfigStore', () => {
    const postconfig = useStorage('PostConfigStore:postconfig', ref({
        category: '',
        category_id: 0,
        dtloading: false,
        kabkota: {}
    }));

    return { postconfig };
});