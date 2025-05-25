import React from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react'
import AdminLayout from '@/Layouts/AdminLayout'
import Pagination from '@/Components/Pagination'

export default function Index({ users, filters }) {
  const { auth } = usePage().props;
  const user = auth.user;

  console.log('Current user:', user);

  const { data, setData, get } = useForm({
    search: filters.search || '',
    role: filters.role || '',
    status: filters.status || '',
  })

  const handleFilter = (e) => {
    e.preventDefault()
  }

  // Add debounced search
  const debouncedSearch = React.useCallback(
    debounce((value) => {
      router.get('/admin/users', {
        search: value,
        role: data.role,
        status: data.status
      }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      })
    }, 300),
    [data.role, data.status]
  )

  // Handle search input change
  const handleSearchChange = (e) => {
    setData('search', e.target.value)
    debouncedSearch(e.target.value)
  }

  // Handle role/status change
  const handleFilterChange = (field, value) => {
    setData(field, value)
    router.get('/admin/users', {
      ...data,
      [field]: value
    }, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }

  // Add debounce utility function
  function debounce(func, wait) {
    let timeout
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout)
        func(...args)
      }
      clearTimeout(timeout)
      timeout = setTimeout(later, wait)
    }
  }

  const toggleStatus = (user) => {
    if (confirm(`Are you sure you want to ${user.is_active ? 'deactivate' : 'activate'} this admin user?`)) {
      router.post(`/admin/users/${user.id}/toggle-status`, {}, {
        preserveScroll: true,
      })
    }
  }

  const destroy = (user) => {
    if (confirm('Are you sure you want to delete this admin user? This action cannot be undone.')) {
      router.delete(`/admin/users/${user.id}`, {
        preserveScroll: true,
      })
    }
  }

  const getRoleClass = (role) => {
    switch (role) {
      case 'manager':
        return 'bg-purple-100 text-purple-800'
      case 'corrector':
        return 'bg-blue-100 text-blue-800'
      case 'general':
        return 'bg-green-100 text-green-800'
      default:
        return 'bg-gray-100 text-gray-800'
    }
  }

  const canManageUsers = user?.role === 'manager';
  const canEditUser = (targetUser) => {
    return user?.role === 'manager' || (user?.role === 'general' && targetUser.id === user.id);
  };

  return (
    <AdminLayout title="Admin User Management">
      <Head title="Admin User Management" />
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          {/* Header */}
          {canManageUsers && (
            <div className="flex justify-between items-center mb-6">
              <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                Admin User Management
              </h2>
              <Link
                href="/admin/users/create"
                className="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              >
                Add New Admin User
              </Link>
            </div>
          )}

          {/* Filters */}
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div className="p-6 bg-white border-b border-gray-200">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label htmlFor="search" className="block text-sm font-medium text-gray-700">Search</label>
                  <input
                    type="text"
                    id="search"
                    value={data.search}
                    onChange={handleSearchChange}
                    placeholder="Search by name or email"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  />
                </div>
                <div>
                  <label htmlFor="role" className="block text-sm font-medium text-gray-700">Role</label>
                  <select
                    id="role"
                    value={data.role}
                    onChange={e => handleFilterChange('role', e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  >
                    <option value="">All Roles</option>
                    <option value="manager">Manager</option>
                    <option value="corrector">Corrector</option>
                    <option value="general">General Admin</option>
                  </select>
                </div>
                <div>
                  <label htmlFor="status" className="block text-sm font-medium text-gray-700">Status</label>
                  <select
                    id="status"
                    value={data.status}
                    onChange={e => handleFilterChange('status', e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  >
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          {/* Users Table */}
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 bg-white border-b border-gray-200">
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {users.data.map(user => (
                      <tr key={user.id}>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="text-sm font-medium text-gray-900">{user.name}</div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="text-sm text-gray-500">{user.email}</div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getRoleClass(user.role)}`}>
                            {user.role}
                          </span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                            {user.is_active ? 'Active' : 'Inactive'}
                          </span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <div className="flex space-x-3">
                            <Link
                              href={`/admin/users/${user.id}/edit`}
                              className="text-indigo-600 hover:text-indigo-900"
                            >
                              Edit
                            </Link>
                            <button
                              onClick={() => toggleStatus(user)}
                              className="text-gray-600 hover:text-gray-900"
                              disabled={canEditUser(user) === false}
                            >
                              {user.is_active ? 'Deactivate' : 'Activate'}
                            </button>
                            <button
                              onClick={() => destroy(user)}
                              className="text-red-600 hover:text-red-900"
                              disabled={canEditUser(user) === false}
                            >
                              Delete
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              {/* Pagination */}
              <div className="mt-4">
                <Pagination links={users.links} />
              </div>
            </div>
          </div>
        </div>
      </div>
    </AdminLayout>
  )
} 