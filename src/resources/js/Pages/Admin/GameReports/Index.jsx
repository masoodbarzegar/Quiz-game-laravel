import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/Pagination';

export default function Index({ auth, gameSessions, clients, filters, success }) {
    const [selectedClientId, setSelectedClientId] = useState(filters.client_id || '');

    const handleFilterChange = (e) => {
        setSelectedClientId(e.target.value);
    };

    useEffect(() => {
        // Debounce or simply apply filter on change
        // For simplicity, applying directly here. Consider debouncing for better UX if many clients.
        const params = { client_id: selectedClientId };
        // Remove empty client_id to avoid empty param in URL
        if (!selectedClientId) {
            delete params.client_id;
        }
        router.get('/admin/game-reports', params, {
            preserveState: true,
            replace: true, // Replace history state instead of pushing new one for filters
        });
    }, [selectedClientId]);

    return (
        <AdminLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Game Reports</h2>}>
            <Head title="Game Reports" />

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
                                <label htmlFor="client_filter" className="block text-sm font-medium text-gray-700 mb-1">Filter by Client:</label>
                                <select 
                                    id="client_filter" 
                                    name="client_filter"
                                    value={selectedClientId}
                                    onChange={handleFilterChange}
                                    className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                >
                                    <option value="">All Clients</option>
                                    {clients.map(client => (
                                        <option key={client.id} value={client.id}>{client.name}</option>
                                    ))}
                                </select>
                            </div>

                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game</th>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Played At</th>
                                        {/* Add other relevant columns like 'Total Time Taken' if available */}
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {gameSessions.data.map((session) => (
                                        <tr key={session.id}>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{session.client?.name || 'N/A'}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{session.game?.title || 'N/A'}</td> {/* Assuming game relation and title */} 
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{session.score === null ? 'N/A' : session.score}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{session.status}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {new Date(session.created_at).toLocaleString()}
                                            </td>
                                            {/* Optional: Link to a detailed report view */}
                                            {/* <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <Link href={route('admin.game-reports.show', session.id)} className="text-indigo-600 hover:text-indigo-900">
                                                    Details
                                                </Link>
                                            </td> */} 
                                        </tr>
                                    ))}
                                    {gameSessions.data.length === 0 && (
                                        <tr>
                                            <td colSpan="5" className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                No game reports found for the selected criteria.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                            <Pagination links={gameSessions.links} className="mt-6" />
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 