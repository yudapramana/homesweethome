<script setup>
import { formatDate } from '../../helper.js';
import { ref } from 'vue';
import { useToastr } from "../../toastr.js";
import { useAuthUserStore } from '../../stores/AuthUserStore';
import { useTableIndexStore } from "../../stores/TableIndexStore";


const tableIndexStore = useTableIndexStore();
const authUserStore = useAuthUserStore();
const fromIndex = ref(0);
const toastr = useToastr();


const props = defineProps({
    user: Object,
    index: Number,
    selectAll: Boolean
});

const emit = defineEmits(['userDeleted', 'editUser', 'confirmUserDeletion']);


const roles = ref([
    {
        name: 'UNASSIGNED',
        value: 0
    },
    {
        name: 'SUPERADMIN',
        value: 1
    },
    {
        name: 'ADMIN',
        value: 2
    },
    {
        name: 'KONTRIBUTOR_UTAMA',
        value: 3
    },
    {
        name: 'KONTRIBUTOR_DAERAH',
        value: 4
    },
    {
        name: 'EDITOR_UTAMA',
        value: 5
    }
]);

const changeRole = (user, current_role_id) => {
    axios.patch(`/api/users/${user.id}/change-role`, {
        current_role_id: current_role_id,
    })
        .then((() => {
            toastr.success('Role changed successfully');
            authUserStore.getAuthUser();
        }));
}

const toggleSelection = () => {
    emit('toggleSelection', props.user);
}

</script>

<template>
    <tr>
        <td><input type="checkbox" :checked="selectAll" @change="toggleSelection" /></td>
        <td>{{ index + 1 }}</td>
        <td>
            {{ user.name }}
            <p class="text-muted m-0 p-0" style="font-size: small !important;">
                {{ user.jabatan }} pada {{ user.kabkota_name }}</p>
        </td>
        <td>{{ user.email }}</td>
        <td>{{ user.formatted_created_at }}</td>
        <td width="20%">
            <template v-if="authUserStore.user.current_role_id == 'SUPERADMIN'">
                <select name="role" id="role" class="form-control" @change="changeRole(user, $event.target.value)"
                    style="width: 200px;">
                    <option v-for="role in roles" :key="role.value" :value="role.value"
                        :selected="user.current_role_id === role.name">{{
            role.name }}</option>

                </select>
            </template>
            <template v-else>
                {{ user.role }}
            </template>

        </td>
        <td>
            <a href="#" @click.prevent="$emit('editUser', user)">
                <i class="fa fa-edit"></i>
            </a>
            <a href="#" @click.prevent="$emit('confirmUserDeletion', user.id)">
                <i class="fa fa-trash text-danger ml-2"></i>
            </a>
        </td>
    </tr>
</template>