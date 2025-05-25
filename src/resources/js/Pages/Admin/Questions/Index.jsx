import React from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/Pagination';

export default function Index({ questions, filters, categories, can }) {
    const { auth } = usePage().props;
    const user = auth.user;

    const { data, setData } = useForm({
        search: filters.search || '',
        status: filters.status || '',
        difficulty: filters.difficulty || '',
        category: filters.category || '',
    });

    // Add debounced search
    const debouncedSearch = React.useCallback(
        debounce((value) => {
            router.get('/admin/questions', {
                search: value,
                status: data.status,
                difficulty: data.difficulty,
                category: data.category,
            }, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 300),
        [data.status, data.difficulty, data.category]
    );

    // Handle search input change
    const handleSearchChange = (e) => {
        setData('search', e.target.value);
        debouncedSearch(e.target.value);
    };

    // Handle filter changes
    const handleFilterChange = (field, value) => {
        setData(field, value);
        router.get('/admin/questions', {
            ...data,
            [field]: value,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    // Add debounce utility function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    const destroy = (question) => {
        if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
            router.delete(`/admin/questions/${question.id}`, {
                preserveScroll: true,
            });
        }
    };

    const approve = (question) => {
        if (confirm('Are you sure you want to approve this question?')) {
            router.post(`/admin/questions/${question.id}/approve`, {}, {
                preserveScroll: true,
            });
        }
    };

    const reject = (question) => {
        const reason = prompt('Please provide a reason for rejection (minimum 10 characters):');
        if (reason && reason.length >= 10) {
            router.post(`/admin/questions/${question.id}/reject`, {
                rejection_reason: reason,
            }, {
                preserveScroll: true,
            });
        } else if (reason !== null) {
            alert('Rejection reason must be at least 10 characters long.');
        }
    };

    const getStatusClass = (status) => {
        switch (status) {
            case 'approved':
                return 'bg-green-100 text-green-800';
            case 'rejected':
                return 'bg-red-100 text-red-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getDifficultyClass = (difficulty) => {
        switch (difficulty) {
            case 'easy':
                return 'bg-green-100 text-green-800';
            case 'medium':
                return 'bg-yellow-100 text-yellow-800';
            case 'hard':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AdminLayout title="Question Management">
            <Head title="Question Management" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Header with Create button */}
                    <div className="mb-6 flex justify-between items-center">
                        <h1 className="text-2xl font-semibold text-gray-900">Question Management</h1>
                        {can.create && (
                            <Link
                                href="/admin/questions/create"
                                className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Create New Question
                            </Link>
                        )}
                    </div>

                    {/* Filters */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                {/* Search */}
                                <div>
                                    <label htmlFor="search" className="block text-sm font-medium text-gray-700">Search</label>
                                    <input
                                        type="text"
                                        id="search"
                                        value={data.search}
                                        onChange={handleSearchChange}
                                        placeholder="Search questions..."
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>

                                {/* Status Filter */}
                                <div>
                                    <label htmlFor="status" className="block text-sm font-medium text-gray-700">Status</label>
                                    <select
                                        id="status"
                                        value={data.status}
                                        onChange={e => handleFilterChange('status', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">All Statuses</option>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>

                                {/* Difficulty Filter */}
                                <div>
                                    <label htmlFor="difficulty" className="block text-sm font-medium text-gray-700">Difficulty</label>
                                    <select
                                        id="difficulty"
                                        value={data.difficulty}
                                        onChange={e => handleFilterChange('difficulty', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">All Difficulties</option>
                                        <option value="easy">Easy</option>
                                        <option value="medium">Medium</option>
                                        <option value="hard">Hard</option>
                                    </select>
                                </div>

                                {/* Category Filter */}
                                <div>
                                    <label htmlFor="category" className="block text-sm font-medium text-gray-700">Category</label>
                                    <select
                                        id="category"
                                        value={data.category}
                                        onChange={e => handleFilterChange('category', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">All Categories</option>
                                        {categories.map((category) => (
                                            <option key={category} value={category}>
                                                {category}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Questions List */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulty</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {questions.data.map((question) => (
                                            <tr key={question.id}>
                                                <td className="px-6 py-4 whitespace-normal">
                                                    <div className="text-sm font-medium text-gray-900">{question.question_text}</div>
                                                    <div className="text-sm text-gray-500">Answer: {question.correct_answer}</div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="text-sm text-gray-900">{question.category || 'Uncategorized'}</div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getDifficultyClass(question.difficulty_level)}`}>
                                                        {question.difficulty_level}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusClass(question.status)}`}>
                                                        {question.status}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="text-sm text-gray-900">{question.creator?.name}</div>
                                                    <div className="text-sm text-gray-500">
                                                        {new Date(question.created_at).toLocaleDateString()}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div className="flex space-x-2">
                                                        {/* View/Edit button */}
                                                        {(
                                                            // Managers and correctors can always edit
                                                            user.role === 'manager' ||
                                                            user.role === 'corrector' ||
                                                            // General can edit only their own pending questions (not rejected or approved)
                                                            (user.role === 'general' && question.created_by === user.id && question.status === 'pending' || question.status === 'rejected')
                                                        ) && (
                                                            <Link
                                                                href={`/admin/questions/${question.id}/edit`}
                                                                className="text-indigo-600 hover:text-indigo-900"
                                                            >
                                                                Edit
                                                            </Link>
                                                        )}

                                                        {/* Approve button (for pending questions) */}
                                                        {can.approve && question.status === 'pending' && (
                                                            <button
                                                                onClick={() => approve(question)}
                                                                className="text-green-600 hover:text-green-900"
                                                            >
                                                                Approve
                                                            </button>
                                                        )}

                                                        {/* Reject button (for pending questions) */}
                                                        {can.approve && question.status === 'pending' && (
                                                            <button
                                                                onClick={() => reject(question)}
                                                                className="text-red-600 hover:text-red-900"
                                                            >
                                                                Reject
                                                            </button>
                                                        )}

                                                        {/* Delete button (for managers) */}
                                                        {user.role === 'manager' && (
                                                            <button
                                                                onClick={() => destroy(question)}
                                                                className="text-red-600 hover:text-red-900"
                                                            >
                                                                Delete
                                                            </button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            <div className="mt-6">
                                <Pagination links={questions.links} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 