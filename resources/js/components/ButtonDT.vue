<template>
    <div>
        <a @click="btnEdit">
            <i class="fa fa-edit"></i>
        </a>
        <a @click="btnDelete">
            <i class="fa fa-trash text-danger ml-2"></i>
        </a>
    </div>
</template>

<script>
export default {
    props : {
        row: Object,
        column: Object
    },
    methods: {
        btnEdit(){
            alert(`Edit ${this.row.id}`);
        },
        btnDelete(){
            alert(`Delete ${this.row.id}`);
        }

    }
}

</script>