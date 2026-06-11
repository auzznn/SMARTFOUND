<template>
  <div class="overflow-x-auto rounded-xl border border-gray-700">
    <table class="admin-table">
      <thead>
        <tr>
          <th v-for="col in columns" :key="col.key" :class="col.thClass">
            {{ col.label }}
          </th>
          <th v-if="hasActions" class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Loading -->
        <tr v-if="loading">
          <td :colspan="columns.length + (hasActions ? 1 : 0)" class="py-12 text-center">
            <div class="flex items-center justify-center gap-2 text-gray-500">
              <span class="inline-block w-5 h-5 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin" />
              Loading...
            </div>
          </td>
        </tr>

        <!-- Empty -->
        <tr v-else-if="!rows.length">
          <td :colspan="columns.length + (hasActions ? 1 : 0)" class="py-12 text-center text-gray-500">
            {{ emptyMessage }}
          </td>
        </tr>

        <!-- Rows -->
        <tr v-else v-for="(row, idx) in rows" :key="row.id || row.uuid || idx">
          <td v-for="col in columns" :key="col.key" :class="col.tdClass">
            <slot :name="'cell-' + col.key" :row="row" :value="getVal(row, col.key)">
              {{ getVal(row, col.key) }}
            </slot>
          </td>
          <td v-if="hasActions" class="text-right">
            <slot name="actions" :row="row" />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
const props = defineProps({
  columns:      { type: Array,   default: () => [] },
  rows:         { type: Array,   default: () => [] },
  loading:      { type: Boolean, default: false },
  emptyMessage: { type: String,  default: 'No data found.' },
  hasActions:   { type: Boolean, default: true }
})

function getVal(row, key) {
  return key.split('.').reduce((obj, k) => obj?.[k], row) ?? '—'
}
</script>
