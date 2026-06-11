<template>
  <div class="page-container max-w-2xl mx-auto">
    <div class="page-header">
      <h1 class="text-3xl font-bold text-white mb-1">Report an Item</h1>
      <p class="text-gray-500">Fill in the details about the lost or found item</p>
    </div>

    <div class="card p-8">
      <form @submit.prevent="handleSubmit" class="space-y-6" novalidate enctype="multipart/form-data">

        <!-- Report Type -->
        <div class="form-group">
          <label>Report Type</label>
          <div class="flex gap-4 mt-1.5">
            <label
              v-for="type in ['lost', 'found']"
              :key="type"
              class="flex items-center gap-2 cursor-pointer"
            >
              <input
                type="radio"
                :value="type"
                v-model="form.reporttype"
                class="w-4 h-4 accent-indigo-600"
              />
              <span
                class="badge text-sm"
                :class="type === 'lost' ? 'badge-lost' : 'badge-found'"
              >
                {{ type.toUpperCase() }}
              </span>
            </label>
          </div>
          <span v-if="errors.reporttype" class="form-error">{{ errors.reporttype }}</span>
        </div>

        <!-- Item Name -->
        <div class="form-group">
          <label for="itemname">Item Name</label>
          <input id="itemname" v-model.trim="form.itemname" type="text"
            placeholder="e.g. Black Laptop Bag, iPhone 14"
            :class="{ 'input-error': errors.itemname }"
            @blur="touch('itemname')" />
          <span v-if="errors.itemname" class="form-error">{{ errors.itemname }}</span>
        </div>

        <!-- Description -->
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" v-model.trim="form.description" rows="3"
            placeholder="Describe the item — colour, brand, any identifiable features..."
            class="w-full resize-none"
            :class="{ 'input-error': errors.description }"
            @blur="touch('description')" />
          <span v-if="errors.description" class="form-error">{{ errors.description }}</span>
        </div>

        <!-- Category + Location row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="form-group">
            <label for="category">Category</label>
            <select id="category" v-model="form.categoryid" class="w-full"
              :class="{ 'input-error': errors.categoryid }"
              @blur="touch('categoryid')">
              <option value="">Select category</option>
              <option v-for="c in categories" :key="c.categoryid" :value="c.categoryid">
                {{ c.category_name }}
              </option>
            </select>
            <span v-if="errors.categoryid" class="form-error">{{ errors.categoryid }}</span>
          </div>

          <div class="form-group">
            <label for="location">Location</label>
            <select id="location" v-model="form.locationid" class="w-full"
              :class="{ 'input-error': errors.locationid }"
              @blur="touch('locationid')">
              <option value="">Select location</option>
              <option v-for="l in locations" :key="l.locationid" :value="l.locationid">
                {{ l.location_name }}
              </option>
            </select>
            <span v-if="errors.locationid" class="form-error">{{ errors.locationid }}</span>
          </div>
        </div>

        <!-- Date + Total Items -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="form-group">
            <label for="date">Date</label>
            <input id="date" v-model="form.date" type="date"
              :max="today"
              :class="{ 'input-error': errors.date }"
              @blur="touch('date')" />
            <span v-if="errors.date" class="form-error">{{ errors.date }}</span>
          </div>

          <div class="form-group">
            <label for="totalitems">Total Items</label>
            <input id="totalitems" v-model.number="form.totalitems" type="number" min="1" max="99"
              placeholder="1" />
          </div>
        </div>

        <!-- Contact Number -->
        <div class="form-group">
          <label for="contactno">Contact Number</label>
          <input id="contactno" v-model.trim="form.contactno" type="tel"
            placeholder="+60 12-345 6789"
            :class="{ 'input-error': errors.contactno }"
            @blur="touch('contactno')" />
          <span v-if="errors.contactno" class="form-error">{{ errors.contactno }}</span>
        </div>

        <!-- Image upload -->
        <div class="form-group">
          <label for="imageInput">Item Photo (optional)</label>
          <div class="mt-1.5 space-y-3">
            <input
              id="imageInput"
              type="file"
              accept="image/*"
              class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer"
            />
            <!-- Preview (jQuery-controlled) -->
            <div id="imagePreview" class="hidden">
              <div class="relative w-48">
                <img id="previewImg" src="" alt="Preview" class="w-48 h-32 object-cover rounded-xl border border-gray-700" />
                <button type="button" id="clearImage"
                  class="absolute -top-2 -right-2 w-6 h-6 bg-red-600 rounded-full text-white text-xs flex items-center justify-center hover:bg-red-700">
                  x
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="flex gap-3 pt-2">
          <button type="submit" class="btn btn-primary flex-1 btn-lg" :disabled="submitting">
            <span v-if="submitting" class="inline-block w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
            {{ submitting ? 'Submitting...' : 'Submit Report' }}
          </button>
          <RouterLink to="/reports" class="btn btn-secondary btn-lg no-underline">Cancel</RouterLink>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute }  from 'vue-router'
import { useReportsStore }      from '@/stores/reports'
import { useToast }             from '@/composables/useToast'
import { validate, required, minLength, validPhone } from '@/utils/validators'
import $ from 'jquery'

const router = useRouter()
const route  = useRoute()
const store  = useReportsStore()
const toast  = useToast()

const submitting = ref(false)
const today = computed(() => new Date().toISOString().split('T')[0])

const form = reactive({
  reporttype:  route.query.type || 'lost',
  itemname:    '',
  description: '',
  categoryid:  '',
  locationid:  '',
  date:        today.value,
  totalitems:  1,
  contactno:   ''
})

const errors = reactive({
  reporttype:  '',
  itemname:    '',
  description: '',
  categoryid:  '',
  locationid:  '',
  date:        '',
  contactno:   ''
})

const categories = computed(() => store.categories)
const locations  = computed(() => store.locations)

let imageFile = null

const rules = {
  reporttype:  [required],
  itemname:    [required, minLength(2)],
  description: [required, minLength(5)],
  categoryid:  [required],
  locationid:  [required],
  date:        [required],
  contactno:   [required, validPhone]
}

function touch(field) {
  errors[field] = validate(form[field], rules[field]) || ''
}

function validateAll() {
  let valid = true
  for (const [field, fieldRules] of Object.entries(rules)) {
    errors[field] = validate(form[field], fieldRules) || ''
    if (errors[field]) valid = false
  }
  return valid
}

async function handleSubmit() {
  if (!validateAll()) return

  submitting.value = true
  try {
    const fd = new FormData()
    fd.append('reporttype',  form.reporttype)
    fd.append('itemname',    form.itemname)
    fd.append('description', form.description)
    fd.append('categoryid',  form.categoryid)
    fd.append('locationid',  form.locationid)
    fd.append('date',        form.date)
    fd.append('totalitems',  form.totalitems)
    fd.append('contactno',   form.contactno)
    if (imageFile) fd.append('image', imageFile)

    const res = await store.createReport(fd)
    const id  = res.report?.reportid || res.reportid
    toast.success('Report submitted successfully!')
    router.push(id ? `/reports/${id}` : '/reports')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to submit report')
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  await Promise.all([store.fetchCategories(), store.fetchLocations()])

  // jQuery image preview (course requirement)
  $('#imageInput').on('change', function () {
    const file = this.files[0]
    if (!file) return
    imageFile = file

    const reader = new FileReader()
    reader.onload = function (e) {
      $('#previewImg').attr('src', e.target.result)
      $('#imagePreview').removeClass('hidden')
    }
    reader.readAsDataURL(file)
  })

  $('#clearImage').on('click', function () {
    $('#imageInput').val('')
    $('#imagePreview').addClass('hidden')
    $('#previewImg').attr('src', '')
    imageFile = null
  })
})
</script>
