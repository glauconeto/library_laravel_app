import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthCard from '../../components/AuthCard';
import AuthCardLogo from '../../components/AuthCardLogo';
import Input from '../../components/Input';
import InputError from '../../components/InputError';
import InputLabel from '../../components/InputLabel';
import PrimaryButton from '../../components/PrimaryButton';

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors } = useForm({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('password.store'));
    };

    return (
        <AuthCard>
            <Head title="Reset Password" />

            <form onSubmit={handleSubmit}>
                <div>
                    <InputLabel htmlFor="email" value="Email" />
                    <Input
                        id="email"
                        type="email"
                        className="mt-1 block w-full"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        autoFocus
                        autoComplete="username"
                    />
                    <InputError className="mt-2" message={errors.email} />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />
                    <Input
                        id="password"
                        type="password"
                        className="mt-1 block w-full"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        required
                        autoComplete="new-password"
                    />
                    <InputError className="mt-2" message={errors.password} />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password_confirmation" value="Confirm Password" />
                    <Input
                        id="password_confirmation"
                        type="password"
                        className="mt-1 block w-full"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        required
                        autoComplete="new-password"
                    />
                    <InputError className="mt-2" message={errors.password_confirmation} />
                </div>

                <div className="flex items-center justify-end mt-4">
                    <PrimaryButton disabled={processing}>
                        Reset Password
                    </PrimaryButton>
                </div>
            </form>
        </AuthCard>
    );
}