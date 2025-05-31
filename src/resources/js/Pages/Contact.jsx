import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

export default function Contact() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        subject: '',
        message: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('contact.submit'), {
            onSuccess: () => reset(),
        });
    };

    return (
        <AppLayout>
            <Head title="Contact Us" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h1 className="text-3xl font-bold text-gray-900 mb-6">Contact Us</h1>

                            <div className="grid md:grid-cols-2 gap-8">
                                {/* Contact Information */}
                                <div className="space-y-6">
                                    <section>
                                        <h2 className="text-2xl font-semibold text-indigo-600 mb-3">Get in Touch</h2>
                                        <p className="text-gray-600 mb-4">
                                            Have questions or feedback? We'd love to hear from you. Fill out the form 
                                            or use our contact information below.
                                        </p>
                                    </section>

                                    <section>
                                        <h3 className="text-lg font-medium text-gray-900 mb-2">Contact Information</h3>
                                        <div className="space-y-3 text-gray-600">
                                            <p className="flex items-center">
                                                <svg className="h-5 w-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                                support@quizgame.com
                                            </p>
                                            <p className="flex items-center">
                                                <svg className="h-5 w-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                                +1 (555) 123-4567
                                            </p>
                                        </div>
                                    </section>

                                    <section>
                                        <h3 className="text-lg font-medium text-gray-900 mb-2">Office Hours</h3>
                                        <div className="space-y-1 text-gray-600">
                                            <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                                            <p>Saturday: 10:00 AM - 4:00 PM</p>
                                            <p>Sunday: Closed</p>
                                        </div>
                                    </section>
                                </div>

                                {/* Contact Form */}
                                <div>
                                    <form onSubmit={submit} className="space-y-6">
                                        <div>
                                            <InputLabel htmlFor="name" value="Name" />
                                            <TextInput
                                                id="name"
                                                type="text"
                                                name="name"
                                                value={data.name}
                                                className="mt-1 block w-full"
                                                autoComplete="name"
                                                isFocused={true}
                                                onChange={(e) => setData('name', e.target.value)}
                                            />
                                            <InputError message={errors.name} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="email" value="Email" />
                                            <TextInput
                                                id="email"
                                                type="email"
                                                name="email"
                                                value={data.email}
                                                className="mt-1 block w-full"
                                                autoComplete="email"
                                                onChange={(e) => setData('email', e.target.value)}
                                            />
                                            <InputError message={errors.email} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="subject" value="Subject" />
                                            <TextInput
                                                id="subject"
                                                type="text"
                                                name="subject"
                                                value={data.subject}
                                                className="mt-1 block w-full"
                                                onChange={(e) => setData('subject', e.target.value)}
                                            />
                                            <InputError message={errors.subject} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="message" value="Message" />
                                            <textarea
                                                id="message"
                                                name="message"
                                                value={data.message}
                                                className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                rows="4"
                                                onChange={(e) => setData('message', e.target.value)}
                                            />
                                            <InputError message={errors.message} className="mt-2" />
                                        </div>

                                        <div className="flex items-center justify-end">
                                            <PrimaryButton disabled={processing}>
                                                Send Message
                                            </PrimaryButton>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
} 