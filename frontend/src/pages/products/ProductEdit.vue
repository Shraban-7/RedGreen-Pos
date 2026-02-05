<script setup>
import { ref, onMounted } from "vue";
import { useToast } from "primevue/usetoast";
import { useApi } from "@/composables/useApi";
import { useRoute, useRouter } from "vue-router";

const api = useApi();
const router = useRouter();
const route = useRoute();
const toast = useToast();

const productId = route.params.id;

// FORM DATA
const form = ref({
  name: "",
  category_id: "",
  brand_id: "",
  buying_price: "",
  selling_price: "",
  stock_in: 0,
  low_stock_quantity: 0,
  description: "",
  sku: "",
});

// DROPDOWNS
const categories = ref([]);
const brands = ref([]);

// THUMBNAIL
const existingThumbnail = ref(null);
const imageFile = ref(null);
const previewImage = ref(null);
const thumbInput = ref(null);

// LOAD PRODUCT + OPTIONS
const loadData = async () => {
  try {
    // Load dropdowns
    const cat = await api.get("/categories");
    categories.value = cat.data.data;

    const br = await api.get("/brands");
    brands.value = br.data.data;

    // Load product
    const res = await api.get(`/products/${productId}`);
    const p = res.data.data;

    form.value = {
      name: p.name,
      category_id: p.category_id,
      brand_id: p.brand_id,
      buying_price: p.buying_price,
      selling_price: p.selling_price,
      stock_in: p.stock_in,
      low_stock_quantity: p.low_stock_quantity,
      description: p.description,
      sku: p.sku,
    };

    existingThumbnail.value = p.thumbnail_url ?? null;
  } catch (err) {
    console.error(err);
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Failed to load product",
      life: 2500,
    });
    router.push("/products");
  }
};

// FILE UPLOAD HANDLERS
const handleImageUpload = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  imageFile.value = file;
  previewImage.value = URL.createObjectURL(file);
};

const handleDrop = (e) => {
  const file = e.dataTransfer.files[0];
  if (!file) return;
  imageFile.value = file;
  previewImage.value = URL.createObjectURL(file);
};

const removeThumbnail = () => {
  imageFile.value = null;
  previewImage.value = null;
  existingThumbnail.value = null;
};

// UPDATE PRODUCT
const updateProduct = async () => {
  try {
    const fd = new FormData();

    Object.keys(form.value).forEach((key) => fd.append(key, form.value[key]));

    if (imageFile.value) {
      fd.append("thumbnail", imageFile.value);
    }

    fd.append("_method", "PUT");

    await api.post(`/products/${productId}`, fd, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    toast.add({
      severity: "success",
      summary: "Success",
      detail: "Product updated successfully",
      life: 1500,
    });

    router.push("/products");
  } catch (err) {
    console.error(err);
    toast.add({
      severity: "error",
      summary: "Failed",
      detail: "Could not update product",
      life: 2500,
    });
  }
};

onMounted(loadData);
</script>

<template>
  <div class="space-y-6">

    <!-- HEADER CARD -->
    <div class="bg-white border rounded-xl shadow p-6">
      <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
      <p class="text-gray-500 text-sm mt-1">Modify product details and save changes.</p>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex gap-6">

      <!-- LEFT CARD (FORM) -->
      <div class="flex-1 bg-white border rounded-xl shadow p-6 space-y-6">

        <h2 class="text-lg font-semibold text-gray-800 mb-2">Product Information</h2>

        <div>
          <label class="block mb-1 font-medium">Product Name</label>
          <input
            v-model="form.name"
            class="w-full p-3 bg-gray-50 border rounded-lg"
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-medium">Category</label>
            <select v-model="form.category_id" class="w-full p-3 bg-gray-50 border rounded-lg">
              <option value="">Select category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block mb-1 font-medium">Brand</label>
            <select v-model="form.brand_id" class="w-full p-3 bg-gray-50 border rounded-lg">
              <option value="">Select brand</option>
              <option v-for="b in brands" :key="b.id" :value="b.id">
                {{ b.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-medium">Buying Price</label>
            <input v-model="form.buying_price" type="number"
              class="w-full p-3 bg-gray-50 border rounded-lg" />
          </div>

          <div>
            <label class="block mb-1 font-medium">Selling Price</label>
            <input v-model="form.selling_price" type="number"
              class="w-full p-3 bg-gray-50 border rounded-lg" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-medium">Initial Stock</label>
            <input v-model="form.stock_in" type="number"
              class="w-full p-3 bg-gray-50 border rounded-lg" />
          </div>

          <div>
            <label class="block mb-1 font-medium">Low Stock Alert</label>
            <input v-model="form.low_stock_quantity" type="number"
              class="w-full p-3 bg-gray-50 border rounded-lg" />
          </div>
        </div>

        <div>
          <label class="block mb-1 font-medium">Description</label>
          <Editor
            v-model="form.description"
            editorStyle="height: 220px"
            class="rounded-lg border bg-white"
          />
        </div>

      </div>

      <!-- RIGHT SIDE -->
      <div class="w-80 space-y-6">

        <!-- THUMBNAIL CARD -->
        <div class="bg-white border rounded-xl shadow p-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">Thumbnail</h2>

          <div
            class="border-2 border-dashed border-gray-300 rounded-xl p-5 text-center cursor-pointer hover:bg-gray-50"
            @click="thumbInput.click()"
            @dragover.prevent
            @drop.prevent="handleDrop"
          >
            <input type="file" ref="thumbInput" class="hidden" accept="image/*" @change="handleImageUpload" />

            <!-- EXISTING THUMBNAIL -->
            <div v-if="existingThumbnail && !previewImage">
              <img :src="existingThumbnail" class="w-40 h-40 object-cover rounded-lg shadow-md border mx-auto" />
              <button
                class="mt-3 px-3 py-1 bg-red-600 text-white rounded-lg text-sm"
                @click.stop="removeThumbnail"
              >
                Remove
              </button>
            </div>

            <!-- PREVIEW (NEW UPLOAD) -->
            <div v-else-if="previewImage" class="relative flex justify-center">
              <img :src="previewImage" class="w-40 h-40 object-cover rounded-lg shadow-md border" />
              <button
                @click.stop="removeThumbnail"
                class="absolute -top-3 -right-3 bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center"
              >
                <i class="pi pi-times text-sm"></i>
              </button>
            </div>

            <!-- EMPTY STATE -->
            <div v-else class="flex flex-col items-center">
              <i class="pi pi-image text-4xl text-gray-400"></i>
              <p class="text-gray-600 mt-2">Click or drag & drop</p>
              <p class="text-xs text-gray-400">PNG, JPG, WEBP (max 2MB)</p>
            </div>
          </div>
        </div>

        <!-- SUMMARY CARD -->
        <div class="bg-white border rounded-xl shadow p-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-3">Summary</h2>

          <ul class="text-sm text-gray-600 space-y-1">
            <li><strong>Name:</strong> {{ form.name }}</li>
            <li><strong>Category:</strong> {{ form.category_id }}</li>
            <li><strong>Brand:</strong> {{ form.brand_id }}</li>
            <li><strong>Price:</strong> {{ form.selling_price }}</li>
            <li><strong>Stock:</strong> {{ form.stock_in }}</li>
            <li><strong>SKU:</strong> {{ form.sku }}</li>
          </ul>
        </div>

      </div>

    </div>

    <!-- ACTION BAR -->
    <div class="bg-white border rounded-xl shadow p-4 flex justify-end gap-3">
      <button
        @click="router.push('/products')"
        class="px-5 py-2 bg-gray-200 rounded-lg hover:bg-gray-300"
      >
        Cancel
      </button>

      <button
        @click="updateProduct"
        class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
      >
        Update Product
      </button>
    </div>

  </div>
</template>
