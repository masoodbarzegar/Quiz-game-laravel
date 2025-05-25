import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import QuestionForm from './Form';

export default function Edit({ question, difficultyLevels, categories }) {
    return (
        <AdminLayout title="Edit Question">
            <Head title="Edit Question" />

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
                            <h2 className="text-lg font-medium text-gray-900 mb-6">Edit Question</h2>
                            
                            {/* Question Status Info */}
                            <div className="mb-6 p-4 bg-gray-50 rounded-md">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Status</p>
                                        <p className="mt-1 text-sm text-gray-900">
                                            <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                                question.status === 'approved' ? 'bg-green-100 text-green-800' :
                                                question.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                                'bg-yellow-100 text-yellow-800'
                                            }`}>
                                                {question.status.charAt(0).toUpperCase() + question.status.slice(1)}
                                            </span>
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Created By</p>
                                        <p className="mt-1 text-sm text-gray-900">{question.creator?.name}</p>
                                    </div>
                                    {question.approver && (
                                        <div>
                                            <p className="text-sm font-medium text-gray-500">Approved By</p>
                                            <p className="mt-1 text-sm text-gray-900">{question.approver.name}</p>
                                            <p className="text-xs text-gray-500">
                                                {new Date(question.approved_at).toLocaleString()}
                                            </p>
                                        </div>
                                    )}
                                    {question.rejecter && (
                                        <div>
                                            <p className="text-sm font-medium text-gray-500">Rejected By</p>
                                            <p className="mt-1 text-sm text-gray-900">{question.rejecter.name}</p>
                                            <p className="text-xs text-gray-500">
                                                {new Date(question.rejected_at).toLocaleString()}
                                            </p>
                                            {question.rejection_reason && (
                                                <p className="mt-1 text-sm text-gray-600">
                                                    Reason: {question.rejection_reason}
                                                </p>
                                            )}
                                        </div>
                                    )}
                                </div>
                            </div>

                            <QuestionForm
                                question={question}
                                difficultyLevels={difficultyLevels}
                                categories={categories}
                                mode="edit"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
} 