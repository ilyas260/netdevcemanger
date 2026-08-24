<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $deviceId = $this->route('device')->id;

        return [
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:50',
            'brand' => 'required|string|max:80',
            'model' => 'required|string|max:100',
            'ip_address' => [
                'required',
                'ip',
                'max:15',
                Rule::unique('devices', 'ip_address')->ignore($deviceId),
            ],
            'location' => 'nullable|string|max:255',
            'agency_id' => 'nullable|exists:agencies,id',
            'snmp_community' => 'nullable|string|max:255',
            'snmp_version' => 'nullable|integer|in:1,2,3',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Set default values and validate subnet matching.
     */
    public function withValidator($validator)
    {
        // Set default snmp_community if empty
        if (!$this->filled('snmp_community')) {
            $this->merge(['snmp_community' => 'public']);
        }

        $validator->after(function ($validator) {
            $agencyId = $this->input('agency_id');
            $ipAddress = $this->input('ip_address');

            if ($agencyId && $ipAddress) {
                $agency = \App\Models\Agency::find($agencyId);
                if ($agency) {
                    $range = $agency->network_address;
                    if (!$range && $agency->router_ip) {
                        if (preg_match('/^(\d+\.\d+\.\d+)\./', $agency->router_ip, $matches)) {
                            $range = $matches[1] . '.0/24';
                        }
                    }

                    if ($range) {
                        if (!$this->ipInRange($ipAddress, $range)) {
                            $validator->errors()->add(
                                'ip_address',
                                "L'adresse IP ({$ipAddress}) n'appartient pas au réseau de l'agence sélectionnée ({$range})."
                            );
                        }
                    }
                }
            }
        });
    }

    /**
     * Helper to verify if an IP belongs to a CIDR range.
     */
    private function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            $range .= '/24';
        }
        list($subnet, $bits) = explode('/', $range);
        $ip_dec = ip2long($ip);
        $subnet_dec = ip2long($subnet);
        if ($ip_dec === false || $subnet_dec === false) {
            return false;
        }
        if ($bits <= 0 || $bits > 32) return false;
        if ($bits == 32) return $ip === $subnet;
        $mask = ~((1 << (32 - $bits)) - 1);
        return ($ip_dec & $mask) === ($subnet_dec & $mask);
    }
}
