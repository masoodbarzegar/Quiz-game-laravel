import React from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react'
import AdminLayout from '@/Layouts/AdminLayout'

export default function Edit({ user: editUser, roles }) {
  const { auth } = usePage().props;
  const user = auth.user;

  const { data, setData, post, processing, errors } = useForm({
    name: editUser.name,
    email: editUser.email,
    password: '',
    password_confirmation: '',
    role: editUser.role,
    is_active: editUser.is_active,
    _method: 'PUT',
  })

  const handleSubmit = (e) => {
    e.preventDefault()
    // Only include password fields if they are not empty
    const formData = { ...data }
    if (!formData.password) {
      delete formData.password
      delete formData.password_confirmation
    }
    post(`/admin/users/${editUser.id}`, {
      ...formData,
      _method: 'PUT'
    }, {
      onSuccess: () => {
        // Optionally redirect to index or show success message
      },
    })
  }

  return (
    <AdminLayout title={`Edit Admin User: ${editUser.name}`}>
      <Head title="Edit Admin User" />
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          {/* Header */}
          <div className="mb-6">
            <Link
              href="/admin/users"
              className="text-indigo-600 hover:text-indigo-900"
            >
              ← Back to Admin Users
            </Link>
          </div>

          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 bg-white border-b border-gray-200">
              <form onSubmit={handleSubmit}>
                <div className="grid grid-cols-1 gap-6">
                  {/* Name */}
                  <div>
                    <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                      Name
                    </label>
                    <input
                      type="text"
                      id="name"
                      value={data.name}
                      onChange={e => setData('name', e.target.value)}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      required
                    />
                    {errors.name && (
                      <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                    )}
                  </div>

                  {/* Email */}
                  <div>
                    <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                      Email
                    </label>
                    <input
                      type="email"
                      id="email"
                      value={data.email}
                      onChange={e => setData('email', e.target.value)}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      required
                    />
                    {errors.email && (
                      <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                    )}
                  </div>

                  {/* Password */}
                  <div>
                    <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                      Password (leave blank to keep current password)
                    </label>
                    <input
                      type="password"
                      id="password"
                      value={data.password}
                      onChange={e => setData('password', e.target.value)}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    {errors.password && (
                      <p className="mt-1 text-sm text-red-600">{errors.password}</p>
                    )}
                  </div>

                  {/* Password Confirmation */}
                  <div>
                    <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">
                      Confirm Password (leave blank to keep current password)
                    </label>
                    <input
                      type="password"
                      id="password_confirmation"
                      value={data.password_confirmation}
                      onChange={e => setData('password_confirmation', e.target.value)}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                  </div>

                  {/* Role */}
                  <div>
                    <label htmlFor="role" className="block text-sm font-medium text-gray-700">
                      Role
                    </label>
                    <select
                      id="role"
                      value={data.role}
                      onChange={e => setData('role', e.target.value)}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      required
                    >
                      <option value="">Select a role</option>
                      {Object.entries(roles).map(([value, label]) => (
                        <option key={value} value={value}>
                          {label}
                        </option>
                      ))}
                    </select>
                    {errors.role && (
                      <p className="mt-1 text-sm text-red-600">{errors.role}</p>
                    )}
                  </div>

                  {/* Active Status */}
                  <div>
                    <label className="flex items-center">
                      <input
                        type="checkbox"
                        checked={data.is_active}
                        onChange={e => setData('is_active', e.target.checked)}
                        className="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      />
                      <span className="ml-2 text-sm text-gray-600">Active</span>
                    </label>
                    {errors.is_active && (
                      <p className="mt-1 text-sm text-red-600">{errors.is_active}</p>
                    )}
                  </div>

                  {/* Submit Button */}
                  <div className="flex justify-end">
                    <button
                      type="submit"
                      disabled={processing}
                      className="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                    >
                      Update Admin User
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </AdminLayout>
  )
} 