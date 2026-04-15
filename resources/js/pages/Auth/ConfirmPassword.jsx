import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthCard from '../../components/AuthCard';
import AuthCardLogo from '../../components/AuthCardLogo';
import Input from '../../components/Input';
import InputError from '../../components/InputError';
import InputLabel from '../../components/InputLabel';
import PrimaryButton from '../../components/PrimaryButton';

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors } = useForm({
        password: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('password.confirm'));
    };

    return (
        <AuthCard>
            <Head title="Confirm Password" />

            <div className="mb-4 text-sm text-gray-600 dark:text-gray-400">
                This is a secure area of the application. Please confirm your password before continuing.
            </div>

            <form onSubmit={handleSubmit}>
                <div>
                    <InputLabel htmlFor="password" value="Password" />
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoFocus
                        onChange={(e) => setData('password', e.target.value)}
                    />
                    <InputError className="mt-2" message={errors.password} />
                </div>

                <div className="flex justify-end mt-4">
                    <PrimaryButton className="ms-4" disabled={processing}>
                        Confirm
                    </PrimaryButton>
                </div>
            </form>
        </AuthCard>
    );
}