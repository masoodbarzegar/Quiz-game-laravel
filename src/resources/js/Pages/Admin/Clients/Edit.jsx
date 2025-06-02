import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Edit({ auth, client }) {
    const { data, setData, put, errors, processing } = useForm({
        name: client.name || '',
        email: client.email || '',
        phone: client.phone || '',
        is_active: client.is_active === undefined ? true : client.is_active, // Default to true if undefined
    });

    const submit = (e) => {
        e.preventDefault();
        put(`/admin/clients/${client.id}`, {
            // onSuccess: () => { /* Optionally redirect or show notification */ }
        });
    };
    
    return (
        <AdminLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Edit Client: {client.name}</h2>}>
            <Head title={`Edit Client - ${client.name}`} />

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
                                        className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                        required
                                    />
                                    {errors.name && <p className="mt-2 text-sm text-red-600">{errors.name}</p>}
                                </div>

                                <div className="mb-4">
                                    <label htmlFor="email" className="block text-sm font-medium text-gray-700">Email</label>
                                    <input 
                                        type="email" 
                                        name="email" 
                                        id="email" 
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                        required
                                    />
                                    {errors.email && <p className="mt-2 text-sm text-red-600">{errors.email}</p>}
                                </div>

                                <div className="mb-4">
                                    <label htmlFor="phone" className="block text-sm font-medium text-gray-700">Phone</label>
                                    <input 
                                        type="text" 
                                        name="phone" 
                                        id="phone" 
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    />
                                    {errors.phone && <p className="mt-2 text-sm text-red-600">{errors.phone}</p>}
                                </div>

                                <div className="mb-4">
                                    <label htmlFor="is_active" className="block text-sm font-medium text-gray-700">Status</label>
                                    <select 
                                        name="is_active" 
                                        id="is_active"
                                        value={data.is_active.toString()} // Ensure value is string for select
                                        onChange={(e) => setData('is_active', e.target.value === 'true')} // Convert back to boolean
                                        className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    >
                                        <option value="true">Active</option>
                                        <option value="false">Inactive</option>
                                    </select>
                                    {errors.is_active && <p className="mt-2 text-sm text-red-600">{errors.is_active}</p>}
                                </div>

                                <div className="flex items-center justify-end mt-6">
                                    <Link href={`/admin/clients`} className="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                        Cancel
                                    </Link>
                                    <button 
                                        type="submit" 
                                        className="px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
                                        disabled={processing}
                                    >
                                        Update Client
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