<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head,Link,router} from '@inertiajs/vue3';
    import { reactive } from 'vue'

    const form = reactive({
        url: null,
    })

    function submit() {
        router.get(route('etsy.index'), form);
    }


    defineProps({
        etsy_exsists: Boolean,
        data: Object,
    });
</script>

<template>

    <Head title="Etsy" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Etsy</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="relative flex flex-col items-top justify-center min-h-[50vh] bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0">
                        <div class="flex flex-row items-center">
                            <h3 v-if="etsy_exsists" class="mr-2">You're connected to etsy</h3>
                            <a :href="route('etsy.connect')" class="bg-[#333] p-3 text-white rounded focus:outline-none focus:shadow-outline">{{ etsy_exsists ? "Reconnect to etsy" : "Connect to etsy" }}</a>
                        </div>
                        <div class="flex flex-col mt-5" v-if="etsy_exsists">
                            <form class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4" @submit.prevent="submit">
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2" for="url">Url:</label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="url" type="text" v-model="form.url" />
                                </div>
                                <button class="bg-[#333] p-3 text-white rounded rounded focus:outline-none focus:shadow-outline" type="submit">Submit</button>
                            </form>
                        </div>
                        <div v-if="data">
                            <p v-if="data.error">{{ data.error }}</p>
                            <p v-else>{{ data }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
