/**
 * Validators for SmartFound forms.
 * Each validator returns true on pass, or an error string on failure.
 */

export const required = (val) =>
  !!val?.toString().trim() || 'This field is required'

export const minLength = (n) => (val) =>
  (val?.length >= n) || `Minimum ${n} characters`

export const maxLength = (n) => (val) =>
  (val?.length <= n) || `Maximum ${n} characters`

export const passwordMatch = (pass) => (val) =>
  val === pass || 'Passwords do not match'

export const validPhone = (val) =>
  /^[0-9+\-\s()]{7,20}$/.test(val) || 'Invalid phone number'

export const validUsername = (val) =>
  /^[a-zA-Z0-9_]{3,30}$/.test(val) ||
  'Username must be 3-30 characters (letters, numbers, underscore)'

export const validEmail = (val) =>
  /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || 'Invalid email address'

/**
 * Run a list of validator functions against a value.
 * Returns the first error string, or null if all pass.
 *
 * @param {any} value
 * @param {Function[]} rules
 * @returns {string|null}
 */
export const validate = (value, rules) => {
  for (const rule of rules) {
    const result = rule(value)
    if (result !== true) return result
  }
  return null
}

/**
 * Validate an entire form object.
 * schema: { fieldName: [rule1, rule2, ...] }
 * Returns { fieldName: errorString | null }
 */
export const validateForm = (data, schema) => {
  const errors = {}
  let valid = true
  for (const [field, rules] of Object.entries(schema)) {
    const err = validate(data[field], rules)
    errors[field] = err
    if (err) valid = false
  }
  return { errors, valid }
}
