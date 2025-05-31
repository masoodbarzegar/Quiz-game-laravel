import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function About() {
    return (
        <AppLayout>
            <Head title="About Us" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h1 className="text-3xl font-bold text-gray-900 mb-6">About Our Quiz Platform</h1>
                            
                            <div className="space-y-6">
                                <section>
                                    <h2 className="text-2xl font-semibold text-indigo-600 mb-3">Our Mission</h2>
                                    <p className="text-gray-600 leading-relaxed">
                                        We're dedicated to making learning fun and engaging through interactive quiz games. 
                                        Our platform combines education with entertainment, creating an environment where 
                                        knowledge acquisition becomes an enjoyable experience.
                                    </p>
                                </section>

                                <section>
                                    <h2 className="text-2xl font-semibold text-indigo-600 mb-3">What We Offer</h2>
                                    <div className="grid md:grid-cols-2 gap-6 mt-4">
                                        <div className="bg-gray-50 p-4 rounded-lg">
                                            <h3 className="text-lg font-medium text-gray-900 mb-2">Interactive Quizzes</h3>
                                            <p className="text-gray-600">
                                                Engage with our diverse collection of quizzes covering various topics, 
                                                designed to challenge and entertain.
                                            </p>
                                        </div>
                                        <div className="bg-gray-50 p-4 rounded-lg">
                                            <h3 className="text-lg font-medium text-gray-900 mb-2">Real-time Feedback</h3>
                                            <p className="text-gray-600">
                                                Get immediate feedback on your answers and learn from detailed explanations 
                                                for each question.
                                            </p>
                                        </div>
                                        <div className="bg-gray-50 p-4 rounded-lg">
                                            <h3 className="text-lg font-medium text-gray-900 mb-2">Progress Tracking</h3>
                                            <p className="text-gray-600">
                                                Monitor your learning journey with detailed statistics and progress reports.
                                            </p>
                                        </div>
                                        <div className="bg-gray-50 p-4 rounded-lg">
                                            <h3 className="text-lg font-medium text-gray-900 mb-2">Community Features</h3>
                                            <p className="text-gray-600">
                                                Connect with other learners, share achievements, and compete in friendly 
                                                challenges.
                                            </p>
                                        </div>
                                    </div>
                                </section>

                                <section>
                                    <h2 className="text-2xl font-semibold text-indigo-600 mb-3">Why Choose Us</h2>
                                    <ul className="list-disc list-inside space-y-2 text-gray-600">
                                        <li>User-friendly interface designed for optimal learning experience</li>
                                        <li>Regularly updated content to keep you engaged</li>
                                        <li>Secure and reliable platform for your learning journey</li>
                                        <li>Supportive community of learners and educators</li>
                                        <li>Accessible on multiple devices for learning on the go</li>
                                    </ul>
                                </section>

                                <section className="bg-indigo-50 p-6 rounded-lg">
                                    <h2 className="text-2xl font-semibold text-indigo-600 mb-3">Ready to Start?</h2>
                                    <p className="text-gray-600 mb-4">
                                        Join our community of learners today and start your journey of knowledge discovery!
                                    </p>
                                    <a
                                        href={"/register"}
                                        className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Get Started
                                    </a>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
} 