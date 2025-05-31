import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function GamesIndex({ games }) {
    const [searchQuery, setSearchQuery] = useState('');

    const difficulties = {
        easy: 'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        hard: 'bg-red-100 text-red-800'
    };

    const filteredGames = games.filter(game => 
        game.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        game.description.toLowerCase().includes(searchQuery.toLowerCase())
    );

    return (
        <AppLayout>
            <Head title="Games - QuizGame" />

            <div className="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="sm:flex sm:items-center sm:justify-between mb-8">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Available Games</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Choose from our collection of interactive quiz games
                        </p>
                    </div>
                </div>

                {/* Search */}
                <div className="mb-8">
                    <div className="max-w-lg">
                        <label htmlFor="search" className="sr-only">Search games</label>
                        <div className="relative">
                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="search"
                                name="search"
                                id="search"
                                className="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Search games..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                            />
                        </div>
                    </div>
                </div>

                {/* Games Grid */}
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {filteredGames.map((game) => (
                        <Link
                            key={game.id}
                            href={`/games/${game.slug}`}
                            className="block bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200"
                        >
                            <div className="relative h-48 bg-gray-200 rounded-t-lg overflow-hidden">
                                {game.image ? (
                                    <img
                                        src={game.image}
                                        alt={game.name}
                                        className="w-full h-full object-cover"
                                    />
                                ) : (
                                    <div className="absolute inset-0 flex items-center justify-center bg-indigo-100">
                                        <span className="text-2xl font-bold text-indigo-600">
                                            {game.name}
                                        </span>
                                    </div>
                                )}
                            </div>
                            <div className="p-4">
                                <div className="flex items-center justify-between">
                                    <h3 className="text-lg font-medium text-gray-900">
                                        {game.name}
                                    </h3>
                                    <span className={`px-2 py-1 text-xs font-medium rounded-full ${difficulties[game.difficulty.toLowerCase()]}`}>
                                        {game.difficulty}
                                    </span>
                                </div>
                                <p className="mt-2 text-sm text-gray-500 line-clamp-2">
                                    {game.description}
                                </p>
                                <div className="mt-4 flex items-center justify-between text-sm text-gray-500">
                                    <div className="flex items-center">
                                        <svg className="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{game.time_limit} min</span>
                                    </div>
                                    <div className="flex items-center">
                                        <svg className="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{game.question_count} questions</span>
                                    </div>
                                </div>
                                {game.stats && (
                                    <div className="mt-4 pt-4 border-t border-gray-200">
                                        <div className="grid grid-cols-3 gap-2 text-xs text-gray-500">
                                            <div>
                                                <div className="font-medium text-gray-900">{game.stats.total_players}</div>
                                                <div>Players</div>
                                            </div>
                                            <div>
                                                <div className="font-medium text-gray-900">{game.stats.average_score}</div>
                                                <div>Avg Score</div>
                                            </div>
                                            <div>
                                                <div className="font-medium text-gray-900">{game.stats.completion_rate}</div>
                                                <div>Complete</div>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </Link>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
} 