import React from 'react';
import MainLayout from '@/Layouts/MainLayout';

const Home = () => {
    return (
        <MainLayout>
            <div className="space-y-8">
                {/* Hero Section */}
                <div className="card">
                    <div className="p-8">
                        <h1 className="text-4xl font-bold text-gray-900 mb-4">Welcome to Our Quiz Game</h1>
                        <p className="text-xl text-gray-600 mb-6">
                            Test your knowledge with our exciting collection of quizzes!
                        </p>
                        <button className="btn">
                            Start Quiz
                        </button>
                    </div>
                </div>

                {/* Features Section */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="card p-6">
                        <h2 className="text-xl font-semibold text-gray-900 mb-3">Multiple Categories</h2>
                        <p className="text-gray-600">
                            Choose from a wide range of topics including science, history, and more.
                        </p>
                    </div>
                    <div className="card p-6">
                        <h2 className="text-xl font-semibold text-gray-900 mb-3">Track Progress</h2>
                        <p className="text-gray-600">
                            Monitor your performance and see how you improve over time.
                        </p>
                    </div>
                    <div className="card p-6">
                        <h2 className="text-xl font-semibold text-gray-900 mb-3">Compete with Friends</h2>
                        <p className="text-gray-600">
                            Challenge your friends and compare scores on the leaderboard.
                        </p>
                    </div>
                </div>

                {/* Call to Action */}
                <div className="card bg-blue-50">
                    <div className="p-8 text-center">
                        <h2 className="text-2xl font-bold text-gray-900 mb-4">Ready to Test Your Knowledge?</h2>
                        <p className="text-gray-600 mb-6">
                            Join thousands of players who are already enjoying our quiz game.
                        </p>
                        <button className="btn bg-blue-600 hover:bg-blue-700">
                            Get Started Now
                        </button>
                    </div>
                </div>
            </div>
        </MainLayout>
    );
};

export default Home; 