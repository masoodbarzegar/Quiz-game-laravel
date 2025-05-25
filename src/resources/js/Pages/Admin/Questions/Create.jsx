import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import QuestionForm from './Form';

export default function Create({ difficultyLevels, categories }) {
    return (
        <AdminLayout title="Create Question">
            <Head title="Create Question" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-6">
                        <Link
                            href="/admin/questions"
                            className="text-indigo-600 hover:text-indigo-900"
                        >
                            ← Back to Questions
                        </Link>
                    </div>

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <h2 className="text-lg font-medium text-gray-900 mb-6">Create New Question</h2>
                            <QuestionForm
                                difficultyLevels={difficultyLevels}
                                categories={categories}
                                mode="create"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 