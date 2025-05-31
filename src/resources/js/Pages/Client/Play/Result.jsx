import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatTime } from '@/utils/time';

export default function Result({ game, session, answers }) {
    const getEndReasonText = (reason) => {
        switch (reason) {
            case 'timer':
                return 'Time ran out';
            case 'lives_exhausted':
                return 'No lives remaining';
            case 'completed':
                return 'All questions completed';
            case 'user_exit':
                return 'Game ended by player';
            default:
                return 'Game ended';
        }
    };
    console.log(session)
    const getScoreColor = (score) => {
        const maxScore = 100; // 10 easy (3pts) + 6 medium (5pts) + 5 hard (8pts) = 100 points
        const percentage = (score / maxScore) * 100;
        
        if (percentage >= 80) return 'text-green-600';
        if (percentage >= 60) return 'text-yellow-600';
        return 'text-red-600';
    };

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleString();
    };

    // Calculate total time taken (in seconds)
    const totalTimeTaken = session.total_time_taken || 0;
    
    // Calculate average time per question
    const avgTimePerQuestion = session.questions_answered > 0 
        ? Math.round(totalTimeTaken / session.questions_answered) 
        : 0;

    // Calculate score breakdown by difficulty
    const scoreBreakdown = (session.exam_data || []).reduce((acc, answer) => {
        if (answer.is_correct) {
            let points = 0;
            switch(answer.difficulty_level) {
                case 'easy':
                    points = 3;  // Level 1: 3 points
                    break;
                case 'medium':
                    points = 5;  // Level 2: 5 points
                    break;
                case 'hard':
                    points = 8;  // Level 3: 8 points
                    break;
                default:
                    points = 0;
            }
            acc[answer.difficulty_level] = (acc[answer.difficulty_level] || 0) + points;
        }
        return acc;
    }, {});

    // Calculate maximum possible scores for each difficulty
    const maxScores = {
        easy: 30,    // 10 questions * 3 points
        medium: 30,  // 6 questions * 5 points
        hard: 40     // 5 questions * 8 points
    };

    return (
        <AuthenticatedLayout>
            <Head title={`${game.name} - Results`} />

            <div className="min-h-screen bg-gray-100 py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="text-center mb-8">
                        <h1 className="text-3xl font-bold text-gray-900">{game.name}</h1>
                        <p className="mt-2 text-lg text-gray-600">Game Results</p>
                    </div>

                    {/* Score Card */}
                    <div className="bg-white shadow rounded-lg mb-8">
                        <div className="px-4 py-5 sm:p-6">
                            <div className="text-center">
                                <h2 className="text-2xl font-bold mb-4">Final Score</h2>
                                <div className={`text-5xl font-bold mb-2 ${getScoreColor(session.score)}`}>
                                    {session.score} / 100
                                </div>
                                <p className="text-gray-600">
                                    {session.correct_answers} correct out of {session.questions_answered} questions
                                </p>
                            </div>

                            {/* Score Breakdown */}
                            <div className="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="bg-green-50 p-4 rounded-lg">
                                    <h3 className="font-semibold text-green-800">Level 1 (Easy)</h3>
                                    <p className="text-2xl font-bold text-green-600">
                                        {scoreBreakdown.easy || 0} / {maxScores.easy}
                                    </p>
                                    <p className="text-sm text-green-600">3 points each (10 questions)</p>
                                </div>
                                <div className="bg-yellow-50 p-4 rounded-lg">
                                    <h3 className="font-semibold text-yellow-800">Level 2 (Medium)</h3>
                                    <p className="text-2xl font-bold text-yellow-600">
                                        {scoreBreakdown.medium || 0} / {maxScores.medium}
                                    </p>
                                    <p className="text-sm text-yellow-600">5 points each (6 questions)</p>
                                </div>
                                <div className="bg-red-50 p-4 rounded-lg">
                                    <h3 className="font-semibold text-red-800">Level 3 (Hard)</h3>
                                    <p className="text-2xl font-bold text-red-600">
                                        {scoreBreakdown.hard || 0} / {maxScores.hard}
                                    </p>
                                    <p className="text-sm text-red-600">8 points each (5 questions)</p>
                                </div>
                            </div>

                            {/* Game Details */}
                            <div className="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="bg-gray-50 p-4 rounded-lg">
                                    <h3 className="font-semibold text-gray-800">Game Status</h3>
                                    <p className="text-gray-600">{getEndReasonText(session.end_reason)}</p>
                                    <p className="text-gray-600">Completed at: {formatDate(session.ended_at)}</p>
                                </div>
                                <div className="bg-gray-50 p-4 rounded-lg">
                                    <h3 className="font-semibold text-gray-800">Time Details</h3>
                                    <p className="text-gray-600">Total time: {formatTime(totalTimeTaken)}</p>
                                    <p className="text-gray-600">Average time per question: {formatTime(avgTimePerQuestion)}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Action Buttons */}
                    <div className="flex justify-center space-x-4">
                        <Link
                            href="/profile"
                            className="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500"
                        >
                            Back to Profile
                        </Link>
                        <Link
                            href={`/games/${game.slug}`}
                            className="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                        >
                            Back to Game
                        </Link>
                        <Link
                            href={`/play/${game.slug}/start`}
                            className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500"
                        >
                            Play Again
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 