import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout'; // Assuming you have an AdminLayout
import Pagination from '@/Components/Pagination'; // Assuming a Pagination component

export default function Index({ auth, clients, success }) { // Added success for flash messages
    const { delete: destroy, post, data, setData, errors, processing } = useForm();

    const toggleActive = (client) => {
        post(`/admin/clients/${client.id}/toggle-active`, {
            data: { is_active: !client.is_active },
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => { /* Maybe show a notification */ },
            onError: () => { /* Handle error */ },
        });
    };
    console.log('clients');
    console.log(clients.data[0]);

    return (
        <AdminLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Clients</h2>}>
            <Head title="Clients" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {success && (
                        <div className="mb-4 p-4 bg-green-100 text-green-700 border border-green-300 rounded">
                            {success}
                        </div>
                    )}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <div className="mb-4">
                                <Link href={'/admin/clients/create'} className="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Create Client
                                </Link>
                            </div>
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game Sessions</th>
                                    </tr>
                                </thead>
                                
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {clients.data.map((client) => (
                                        <tr key={client.id}>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{client.name}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{client.email}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{client.phone || 'N/A'}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm">
                                                <button 
                                                    onClick={() => toggleActive(client)}
                                                    className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                                        client.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                                    }`}
                                                    disabled={processing}
                                                >
                                                    {client.is_active ? 'Active' : 'Inactive'}
                                                </button>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <Link href={`/admin/clients/${client.id}/edit`} className="text-indigo-600 hover:text-indigo-900 mr-3">
                                                    Edit
                                                </Link>
                                                {/* Delete button can be added here if needed */}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                {client.game_sessions && client.game_sessions.length > 0 ? (
                                                    <Link href={`/admin/game-reports?client_id=${client.id}`} className="text-indigo-600 hover:text-indigo-900 mr-3">View Game Sessions</Link>
                                                ) : (
                                                    'No game sessions'
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                    {clients.data.length === 0 && (
                                        <tr>
                                            <td colSpan="6" className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                No clients found.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                            <Pagination links={clients.links} className="mt-6" />
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 