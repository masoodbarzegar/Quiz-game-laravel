import React, { useEffect } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Dashboard({ stats, roleData }) {
    // Get page props at the top level of the component
    const page = usePage();
    const { auth, url = '' } = page.props;
    const user = auth?.user;

    // Debug logging in useEffect
    useEffect(() => {
        console.log('Dashboard mounted with props:', {
            stats,
            roleData,
            auth,
            user,
            fullProps: page.props
        });
    }, [stats, roleData, auth, user, page.props]);

    // If no user is found, show a message
    if (!user) {
        console.warn('No authenticated user found in Dashboard component');
        return (
            <AdminLayout title="Dashboard">
                <Head title="Admin Dashboard" />
                <div className="p-4 bg-yellow-50 border-l-4 border-yellow-400">
                    <div className="flex">
                        <div className="flex-shrink-0">
                            <svg className="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                            </svg>
                        </div>
                        <div className="ml-3">
                            <p className="text-sm text-yellow-700">
                                You are not authenticated. Please <a href="/admin/login" className="font-medium underline text-yellow-700 hover:text-yellow-600">log in</a> to access the dashboard.
                            </p>
                        </div>
                    </div>
                </div>
            </AdminLayout>
        );
    }

    const getRoleClass = (role) => {
        switch (role) {
            case 'manager':
                return 'bg-purple-100 text-purple-800';
            case 'corrector':
                return 'bg-blue-100 text-blue-800';
            case 'general':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AdminLayout title="Dashboard">
            <Head title="Admin Dashboard" />

            {/* Stats Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div className="bg-white overflow-hidden shadow rounded-lg">
                    <div className="p-5">
                        <div className="text-sm font-medium text-gray-500">Total Clients</div>
                        <div className="mt-1 text-3xl font-semibold text-gray-900">{stats.total_clients}</div>
                    </div>
                </div>
                <div className="bg-white overflow-hidden shadow rounded-lg">
                    <div className="p-5">
                        <div className="text-sm font-medium text-gray-500">Active Clients</div>
                        <div className="mt-1 text-3xl font-semibold text-gray-900">{stats.active_clients}</div>
                    </div>
                </div>
                <div className="bg-white overflow-hidden shadow rounded-lg">
                    <div className="p-5">
                        <div className="text-sm font-medium text-gray-500">Total Questions</div>
                        <div className="mt-1 text-3xl font-semibold text-gray-900">{stats.total_questions}</div>
                    </div>
                </div>
                <div className="bg-white overflow-hidden shadow rounded-lg">
                    <div className="p-5">
                        <div className="text-sm font-medium text-gray-500">Pending Questions</div>
                        <div className="mt-1 text-3xl font-semibold text-gray-900">{stats.pending_questions}</div>
                    </div>
                </div>
            </div>

            {/* Role-specific content */}
            {user?.role === 'manager' && (
                <>
                    {/* Recent Clients Table */}
                    {roleData?.recent_clients && (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6 bg-white border-b border-gray-200">
                                <div className="flex justify-between items-center mb-4">
                                    <h2 className="text-lg font-medium text-gray-900">Recent Clients</h2>
                                    <Link
                                        href="/admin/clients"
                                        className="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                    >
                                        View All Clients →
                                    </Link>
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {roleData.recent_clients.map(client => (
                                                <tr key={client.id}>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-medium text-gray-900">{client.name}</div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm text-gray-500">{client.email}</div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${client.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                            {client.is_active ? 'Active' : 'Inactive'}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {new Date(client.created_at).toLocaleDateString()}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    )}
                </>
            )}

            {/* Corrector-specific content */}
            {user?.role === 'corrector' && roleData?.pending_questions && (
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div className="p-6 bg-white border-b border-gray-200">
                        <div className="flex justify-between items-center mb-4">
                            <h2 className="text-lg font-medium text-gray-900">Pending Questions</h2>
                            <Link
                                href="/admin/questions"
                                className="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                            >
                                View All Questions →
                            </Link>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulty</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {roleData.pending_questions.map(question => (
                                        <tr key={question.id}>
                                            <td className="px-6 py-4">
                                                <div className="text-sm font-medium text-gray-900">{question.question_text}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {question.category}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {question.difficulty_level}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {new Date(question.created_at).toLocaleDateString()}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
} 