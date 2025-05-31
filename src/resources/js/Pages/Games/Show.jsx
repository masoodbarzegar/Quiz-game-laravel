import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function GameShow({ game, auth }) {
    const difficulties = {
        easy: 'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        hard: 'bg-red-100 text-red-800'
    };

    return (
        <AppLayout>
            <Head title={`${game.name} - QuizGame`} />

            <div className="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                {/* Game Header */}
                <div className="bg-white shadow rounded-lg overflow-hidden">
                    <div className="relative h-64 bg-gray-200">
                        {game.image ? (
                            <img
                                src={game.image}
                                alt={game.name}
                                className="w-full h-full object-cover"
                            />
                        ) : (
                            <div className="absolute inset-0 flex items-center justify-center bg-indigo-100">
                                <span className="text-3xl font-bold text-indigo-600">
                                    {game.name}
                                </span>
                            </div>
                        )}
                    </div>
                    <div className="p-6">
                        <div className="flex items-center justify-between">
                            <h1 className="text-3xl font-bold text-gray-900">
                                {game.name}
                            </h1>
                            <span className={`px-3 py-1 text-sm font-medium rounded-full ${difficulties[game.difficulty.toLowerCase()]}`}>
                                {game.difficulty}
                            </span>
                        </div>
                        <p className="mt-4 text-lg text-gray-600">
                            {game.long_description}
                        </p>
                    </div>
                </div>

                {/* Game Details */}
                <div className="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Game Stats */}
                        <div className="bg-white shadow rounded-lg p-6">
                            <h2 className="text-lg font-medium text-gray-900 mb-4">Game Statistics</h2>
                            <div className="grid grid-cols-3 gap-4">
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-indigo-600">{game.stats.total_players}</div>
                                    <div className="text-sm text-gray-500">Total Players</div>
                                </div>
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-indigo-600">{game.stats.average_score}</div>
                                    <div className="text-sm text-gray-500">Average Score</div>
                                </div>
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-indigo-600">{game.stats.completion_rate}</div>
                                    <div className="text-sm text-gray-500">Completion Rate</div>
                                </div>
                            </div>
                        </div>

                        {/* Game Rules */}
                        <div className="bg-white shadow rounded-lg p-6">
                            <h2 className="text-lg font-medium text-gray-900 mb-4">Game Rules</h2>
                            <ul className="space-y-3">
                                {game.rules.map((rule, index) => (
                                    <li key={index} className="flex items-start">
                                        <svg className="h-6 w-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span className="text-gray-600">{rule}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        {/* Topics */}
                        <div className="bg-white shadow rounded-lg p-6">
                            <h2 className="text-lg font-medium text-gray-900 mb-4">Topics Covered</h2>
                            <div className="flex flex-wrap gap-2">
                                {game.topics.map((topic, index) => (
                                    <span
                                        key={index}
                                        className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800"
                                    >
                                        {topic}
                                    </span>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Game Info */}
                        <div className="bg-white shadow rounded-lg p-6">
                            <h2 className="text-lg font-medium text-gray-900 mb-4">Game Information</h2>
                            <div className="space-y-4">
                                <div className="flex items-center text-gray-600">
                                    <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Time Limit: {game.time_limit} minutes</span>
                                </div>
                                <div className="flex items-center text-gray-600">
                                    <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Questions: {game.question_count}</span>
                                </div>
                                <div className="flex items-center text-gray-600">
                                    <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Points per Question: {game.points_per_question}</span>
                                </div>
                                <div className="flex items-center text-gray-600">
                                    <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>Skip Limit: {game.skip_limit}</span>
                                </div>
                            </div>
                        </div>

                        {/* Action Button */}
                        <div className="bg-white shadow rounded-lg p-6">
                            {auth?.user ? (
                                <Link
                                    href={"/play/" + game.slug + "/start"}
                                    className="w-full flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Start Game
                                </Link>
                            ) : (
                                <div className="space-y-4">
                                    <p className="text-center text-gray-600">
                                        Sign in to start playing this game
                                    </p>
                                    <div className="flex flex-col space-y-3">
                                        <Link
                                            href={"/login"}
                                            className="w-full flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            Log in to Play
                                        </Link>
                                        <Link
                                            href={"/register"}
                                            className="w-full flex items-center justify-center px-4 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            Create Account
                                        </Link>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
} 