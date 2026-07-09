<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Notification\NotificationsServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Notification\ListNotificationsRequest;
use App\Http\Resources\V1\NotificationResource;
use App\Models\V1\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function __construct(
        private readonly NotificationsServiceInterface $notificationsService
    ){}

    public function index(ListNotificationsRequest $request): JsonResponse
    {
        $store     = $request->attributes->get('store');
        $validated = $request->validated();
        $perPage   = (int) ($validated['per_page'] ?? 25);

        $notifications = $this->notificationsService->list(
            storeId: (int) $store->id,
            unread:  $request->boolean('unread'),
            perPage: $perPage
        );

        return response()->json([
            'data' => NotificationResource::collection($notifications->getCollection()),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
                'last_page'    => $notifications->lastPage(),
            ]
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $store = $request->attributes->get('store');

        $notification = $this->notificationsService->markAsRead(
            storeId:        (int) $store->id,
            notificationId: $notification->id
        );

        return response()->json([
            'message' => 'Notification marquée comme lue.',
            'data'    => new NotificationResource($notification)
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $store = $request->attributes->get('store');

        $this->notificationsService->markAllAsRead((int) $store->id);

        return response()->json([
            'message' => 'Toutes les notifications ont été marquées comme lues.'
        ]);
    }

    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        $store = $request->attributes->get('store');

        $this->notificationsService->delete(
            storeId:        (int) $store->id,
            notificationId: $notification->id
        );

        return response()->json([
            'message' => 'Notification supprimée.'
        ]);
    }

}
