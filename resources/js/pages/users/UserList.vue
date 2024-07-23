<script setup>
import axios from "axios";
import { ref, onMounted, reactive, watch, toDisplayString } from "vue";
import { Form, Field, useResetForm } from "vee-validate";
import * as yup from "yup";
import UserListItem from "./UserListItem.vue";
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import { useToastr } from "../../toastr.js";
import { useTableIndexStore } from "../../stores/TableIndexStore";


const tableIndexStore = useTableIndexStore();
const toastr = useToastr();


const users = ref({ 'data': [] });
const orgs = ref({ 'data': [] });
const editing = ref(false);
const formValues = ref({
    id: "",
    name: "",
    email: "",
    organization_id: "",
});
const form = ref(null);

const fetchOrgs = () => {
    axios.get(`/api/fetch-orgs`).then((response) => {
        console.log('response');
        console.log(response);
        orgs.value = response;
    });
};

const getUsers = (page = 1) => {
    axios.get(`/api/users?page=${page}`, {
        params: {
            search_query: searchQuery.value
        }
    }).then((response) => {
        users.value = response.data;
        selectedUsers.value = [];
        selectAll.value = false;
    });
};

const createUserSchema = yup.object({
    name: yup.string().required(),
    email: yup.string().email().required(),
    password: yup.string().required().min(8),
});

const editUserSchema = yup.object({
    name: yup.string().required(),
    email: yup.string().email().required(),
    password: yup.string().when((password, schema) => {
        return password != "" ? schema.required().min(8) : schema;
    }),
});

const createUser = (values, { resetForm, setFieldError, setErrors }) => {
    axios
        .post("/api/users", values)
        .then((response) => {
            users.value.data.unshift(response.data);
            $("#defaultModal").modal("hide");
            resetForm();
            toastr.success("User created successfully!");
        })
        .catch((error) => {
            if (error.response.data.errors) {
                setErrors(error.response.data.errors);
            }
        });
};

const addUser = () => {
    editing.value = false;
    form.value.resetForm();
    formValues.value = {
        id: "",
        name: "",
        email: "",
    };
    $("#defaultModal").modal("show");
};

const editUser = (user) => {
    editing.value = true;
    form.value.resetForm();
    $("#defaultModal").modal("show");
    console.log("user");
    console.log(user);
    console.log(user.id);
    console.log(user.name);

    formValues.value = {
        id: user.id,
        name: user.name,
        email: user.email,
        organization_id: user.org_id
    };
};

const updateUser = (values, { setErrors }) => {
    axios.put('/api/users/' + formValues.value.id, values)
        .then((response) => {
            const index = users.value.data.findIndex(user => user.id === response.data.id);
            users.value.data[index] = response.data;
            $("#defaultModal").modal("hide");
            toastr.success("User updated successfully!");
            authUserStore.getAuthUser();
        })
        .catch((error) => {
            if (error.response) {
                setErrors(error.response.data.errors);
            }
        });
};

const handleSubmit = (values, actions) => {
    console.log(actions);
    if (editing.value) {
        console.log('updateUser');
        updateUser(values, actions);
    } else {
        console.log('createUser');
        createUser(values, actions);
    }
};


const searchQuery = ref(null);

const selectedUsers = ref([]);

const toggleSelection = (user) => {
    const index = selectedUsers.value.indexOf(user.id);
    if (index === -1) {
        selectedUsers.value.push(user.id);
    } else {
        selectedUsers.value.splice(index, 1);
    }
}


const userIdBeingDeleted = ref(null);

const confirmUserDeletion = (id) => {
    userIdBeingDeleted.value = id;
    $("#deleteModal").modal("show");
};

const deleteUser = () => {
    axios.delete(`/api/users/${userIdBeingDeleted.value}`)
        .then(() => {
            $("#deleteModal").modal("hide");
            toastr.success('User deleted Successfully!');
            users.value.data = users.value.data.filter(user => user.id !== userIdBeingDeleted.value);
        })
}

const bulkDelete = () => {
    axios.delete('/api/users', {
        data: {
            ids: selectedUsers.value
        }
    })
        .then(response => {
            users.value.data = users.value.data.filter(user => !selectedUsers.value.includes(user.id));
            selectedUsers.value = [];
            selectAll.value = false;
            toastr.success(response.data.message);
        });
}

const selectAll = ref(false);
const selectAllUsers = () => {
    if (selectAll.value) {
        selectedUsers.value = users.value.data.map(user => user.id);
    } else {
        selectedUsers.value = [];
    }
}

watch(searchQuery, debounce(() => {
    // search();
    getUsers();
}, 300));

onMounted(() => {
    getUsers();
    fetchOrgs();
});
</script>

<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Daftar Pengguna</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Laporan Kerja</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            <div class="d-flex justify-content-between">
                <div class="d-flex">
                    <button @click="addUser" type="button" class="mb-2 btn btn-primary">
                        <i class="fa fa-plus-circle mr-1"></i>
                        Add New User
                    </button>
                    <div v-if="selectedUsers.length > 0">
                        <button @click="bulkDelete" type="button" class="ml-2 mb-2 btn btn-danger">
                            <i class="fa fa-trash" mr-1></i>
                            Delete Selected
                        </button>
                        <span class="ml-2">Selected {{ selectedUsers.length }} users</span>
                    </div>
                </div>

                <div>
                    <input type="text" v-model="searchQuery" class="form-control" placeholder="Search...">

                </div>
            </div>


            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" v-model="selectAll" @change="selectAllUsers" /></th>
                                    <th style="width: 10px">#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Registered Date</th>
                                    <th>Roles</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody v-if="users.data.length > 0">
                                <UserListItem v-for="(user, index) in users.data" :key="user.id" :user="user" :index="index"
                                    @edit-user="editUser" @confirm-user-deletion="confirmUserDeletion"
                                    @toggle-selection="toggleSelection" :select-all="selectAll" />
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td colspan="6" class="text-center">No results found...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <Bootstrap4Pagination :data="users" @pagination-change-page="getUsers" :limit="1" :keepLength="true" />

        </div>
    </div>

    <!-- Default Create Update Modal -->
    <div class="modal fade" id="defaultModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <span v-if="editing">Edit User</span>
                        <span v-else>Add New User</span>
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <Form ref="form" @submit="handleSubmit" :validation-schema="editing ? editUserSchema : createUserSchema"
                    v-slot="{ errors }" :initial-values="formValues">
                    <div class="modal-body">

                        <div class="form-group">
                            <label for="organization_id">Satuan Kerja</label>

                            <Field v-model="formValues.organization_id" name="organization_id" as="select"
                                class="form-control" id="organization_id">
                                <option v-for="org in orgs.data" :value="org.id" :key="org.id">
                                    {{ org.name }}
                                </option>
                            </Field>
                            <span class="invalid-feedback">{{ errors.organization_id }}</span>


                            <!-- <select name="role" id="role" class="form-control"
                                @change="changeRole(user, $event.target.value)">
                                <option v-for="role in roles" :key="role.value" :value="role.value"
                                    :selected="user.role === role.name">{{ role.name }}</option>

                            </select> -->
                        </div>

                        <div class="form-group">
                            <label for="name">Name</label>
                            <Field v-model="formValues.name" type="text" name="name" id="name" class="form-control"
                                :class="{ 'is-invalid': errors.name }" aria-describedby="name"
                                placeholder="Enter full name" />
                            <span class="invalid-feedback">{{ errors.name }}</span>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <Field v-model="formValues.email" type="text" name="email" id="email" class="form-control"
                                :class="{ 'is-invalid': errors.email }" aria-describedby="email"
                                placeholder="Enter email" />
                            <span class="invalid-feedback">{{ errors.email }}</span>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <Field v-model="formValues.password" type="text" name="password" id="password"
                                class="form-control" :class="{ 'is-invalid': errors.password }" aria-describedby="password"
                                placeholder="Enter Password" />
                            <span class="invalid-feedback">{{ errors.password }}</span>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </Form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <span>Delete User</span>
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body">
                    <h5>Are you sure you want to delete this use?</h5>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Close
                    </button>
                    <button @click.prevent="deleteUser" type="submit" class="btn btn-primary">Delete</button>
                </div>

            </div>
        </div>
    </div>
</template>