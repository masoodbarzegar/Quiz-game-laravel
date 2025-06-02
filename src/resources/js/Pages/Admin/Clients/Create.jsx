import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout'; // Assuming you have an AdminLayout

export default function Create({ auth }) {
    const { data, setData, post, errors, processing } = useForm({
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
        is_active: true,
    });

    const submit = (e) => {
        e.preventDefault();
        post('/admin/clients');
    };

    return (
        <AdminLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Create Client</h2>}>
            <Head title="Create Client" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <form onSubmit={submit}>
                                <div className="mb-4">
                                    <label htmlFor="name" className="block text-sm font-medium text-gray-700">Name</label>
                                    <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    />
                                    {errors.name && <div className="text-red-500 text-xs mt-1">{errors.name}</div>}
                                </div>

                                <div className="mb-4">
                                    <label htmlFor="email" className="block text-sm font-medium text-gray-700">Email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        id="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    />
                                    {errors.email && <div className="text-red-500 text-xs mt-1">{errors.email}</div>}
                                </div>

                                <div className="mb-4">
                                    <label htmlFor="phone" className="block text-sm font-medium text-gray-700">Phone</label>
                                    <input
                                        type="text"
                                        name="phone"
                                        id="phone"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    />
                                    {errors.phone && <div className="text-red-500 text-xs mt-1">{errors.phone}</div>}
                                </div>

                                <div className="mb-4">
                                    <label htmlFor="password" className="block text-sm font-medium text-gray-700">Password</label>
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    />
                                    {errors.password && <div className="text-red-500 text-xs mt-1">{errors.password}</div>}
                                </div>

                                <div className="mb-4">
                                    <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">Confirm Password</label>
                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        id="password_confirmation"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    />
                                </div>
                                
                                <div className="mb-4">
                                    <label htmlFor="is_active" className="flex items-center">
                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            id="is_active"
                                            checked={data.is_active}
                                            onChange={(e) => setData('is_active', e.target.checked)}
                                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                        <span className="ml-2 text-sm text-gray-600">Active</span>
                                    </label>
                                    {errors.is_active && <div className="text-red-500 text-xs mt-1">{errors.is_active}</div>}
                                </div>

                                <div className="flex items-center justify-end mt-4">
                                    <Link href={'/admin/clients'} className="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                        Cancel
                                    </Link>
                                    <button
                                        type="submit"
                                        className="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
                                        disabled={processing}
                                    >
                                        Create Client
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 